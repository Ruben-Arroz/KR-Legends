<?php
// ==========================================
// users-adm.php - Enhanced User Administration
// ==========================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();

// 1) Incluir conexão MySQLi (cria $mysqli)
require_once __DIR__ . '/../../databaseconnect.php';

// 2) Verificar permissão (apenas "ADM" e "SUPERADM" podem aceder)
if (!in_array($_SESSION['role'], ['ADM', 'SUPERADM'])) {
    http_response_code(403);
    die('Acesso negado');
}

// Função auxiliar para apagar ficheiros antigos
function apagarFicheiroAnterior(string $filename, string $tipo)
{
    $defaults = ['avatar_default.png', 'banner_default.png'];
    if ($filename && !in_array($filename, $defaults)) {
        $caminho = __DIR__ . "/../Imagens/{$tipo}s/{$filename}";
        if (file_exists($caminho)) {
            unlink($caminho);
        }
    }
}

// ==============================
// REMOVER UTILIZADOR (GET ?delete_id=XX)
// ==============================
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delId = (int) $_GET['delete_id'];

    // 1) Selecionar avatar e banner atuais para apagar
    $stmtImg = $mysqli->prepare("SELECT KR_AVATAR, KR_BANNER FROM KR_USERS WHERE ID = ?");
    $stmtImg->bind_param('i', $delId);
    $stmtImg->execute();
    $resImg = $stmtImg->get_result();
    $rowImg = $resImg->fetch_assoc();
    $oldAvatar = $rowImg['KR_AVATAR'] ?? null;
    $oldBanner = $rowImg['KR_BANNER'] ?? null;
    $stmtImg->close();

    // 2) Apaga ficheiros (se não forem "default")
    apagarFicheiroAnterior($oldAvatar, 'avatar');
    apagarFicheiroAnterior($oldBanner, 'banner');

    // 3) Apaga utilizador (cascade apaga KR_USER_SOCIALS e KR_USER_TOKENS)
    $stmtDel = $mysqli->prepare("DELETE FROM KR_USERS WHERE ID = ?");
    $stmtDel->bind_param('i', $delId);
    $stmtDel->execute();
    $stmtDel->close();

    header('Location: users-adm.php?msg=deleted');
    exit;
}

// ==============================
// INSERIR OU EDITAR UTILIZADOR (POST)
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // -------------------------------------------------
    // A) ADICIONAR UTILIZADOR
    // -------------------------------------------------
    if ($action === 'add_user') {
        $email = trim($_POST['KR_EMAIL_USER'] ?? '');
        $username = trim($_POST['KR_USER_NAME'] ?? '');
        $name = trim($_POST['KR_NAME'] ?? '');
        $password = $_POST['KR_PASSWORD'] ?? '';
        $country = trim($_POST['KR_COUNTRY'] ?? '');
        $city = trim($_POST['KR_CITY'] ?? '');
        $genero = $_POST['KR_GENERO'] ?? 'Prefiro não divulgar';
        $robloxUsername = trim($_POST['ROBLOX_USERNAME'] ?? '');

        // Só SUPERADM pode definir role
        $role = ($_SESSION['role'] === 'SUPERADM' && isset($_POST['KR_ROLE']))
            ? $_POST['KR_ROLE']
            : 'USER';

        // Redes sociais - converter valores vazios para NULL
        $emailSocial = trim($_POST['EMAIL_SOCIAL'] ?? '') ?: null;
        $whatsapp = trim($_POST['WHATSAPP'] ?? '') ?: null;
        $youtube = trim($_POST['YOUTUBE'] ?? '') ?: null;
        $instagram = trim($_POST['INSTAGRAM'] ?? '') ?: null;
        $tiktok = trim($_POST['TIKTOK'] ?? '') ?: null;
        $facebook = trim($_POST['FACEBOOK'] ?? '') ?: null;
        $linkedin = trim($_POST['LINKEDIN'] ?? '') ?: null;
        $github = trim($_POST['GITHUB'] ?? '') ?: null;
        $twitter = trim($_POST['TWITTER'] ?? '') ?: null;

        // 1) Validações básicas
        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !preg_match(
                '/^[A-Za-z0-9._]{3,30}$/'
                ,
                $username
            )
            || strlen($password) < 6
        ) {
            header('Location: users-adm.php?msg=error_invalid');
            exit;
        }

        // 2) Gera hash da password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // 3) Inserir utilizador
        $stmtIns = $mysqli->prepare("
            INSERT INTO KR_USERS
              (KR_EMAIL_USER, KR_USER_NAME, KR_PASSWORD, KR_NAME, KR_COUNTRY, KR_CITY,
               KR_GENERO, KR_ROLE, KR_AVATAR, KR_BANNER, ROBLOX_USERNAME)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?, 'avatar_default.png', 'banner_default.png', ?)
        ");
        $stmtIns->bind_param(
            'sssssssss',
            $email,
            $username,
            $hash,
            $name,
            $country,
            $city,
            $genero,
            $role,
            $robloxUsername
        );
        $stmtIns->execute();
        $newId = $mysqli->insert_id;
        $stmtIns->close();

        // 4) Inserir redes sociais - CORRIGIDO
        $stmtSocials = $mysqli->prepare("
            INSERT INTO KR_USER_SOCIALS
              (SOCIAL_ID, EMAIL_SOCIAL, WHATSAPP, YOUTUBE, INSTAGRAM, TIKTOK, FACEBOOK, LINKEDIN, GITHUB, TWITTER)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtSocials->bind_param(
            'isssssssss',
            $newId,
            $emailSocial,
            $whatsapp,
            $youtube,
            $instagram,
            $tiktok,
            $facebook,
            $linkedin,
            $github,
            $twitter
        );
        $stmtSocials->execute();
        $stmtSocials->close();

        // 5) Processar upload de Avatar
        if (isset($_FILES['KR_AVATAR']) && $_FILES['KR_AVATAR']['error'] === UPLOAD_ERR_OK) {
            $extPerm = ['jpg', 'jpeg', 'png', 'gif'];
            $fileTmp = $_FILES['KR_AVATAR']['tmp_name'];
            $fileName = $_FILES['KR_AVATAR']['name'];
            $sizeOK = $_FILES['KR_AVATAR']['size'] > 0 && $_FILES['KR_AVATAR']['size'] < 5 * 1024 * 1024;
            $pathInfo = pathinfo($fileName);
            $ext = strtolower($pathInfo['extension']);

            if ($sizeOK && in_array($ext, $extPerm)) {
                $avatarName = "avatar_{$newId}.{$ext}";
                $destA = __DIR__ . "/../Imagens/avatares/{$avatarName}";

                // Apaga possíveis avatares antigos
                foreach ($extPerm as $e) {
                    $oldA = __DIR__ . "/../Imagens/avatares/avatar_{$newId}.{$e}";
                    if (file_exists($oldA))
                        unlink($oldA);
                }

                move_uploaded_file($fileTmp, $destA);

                $updA = $mysqli->prepare("UPDATE KR_USERS SET KR_AVATAR = ? WHERE ID = ?");
                $updA->bind_param('si', $avatarName, $newId);
                $updA->execute();
                $updA->close();
            }
        }

        // 6) Processar upload de Banner
        if (isset($_FILES['KR_BANNER']) && $_FILES['KR_BANNER']['error'] === UPLOAD_ERR_OK) {
            $extPerm = ['jpg', 'jpeg', 'png', 'gif'];
            $fileTmp = $_FILES['KR_BANNER']['tmp_name'];
            $fileName = $_FILES['KR_BANNER']['name'];
            $sizeOK = $_FILES['KR_BANNER']['size'] > 0 && $_FILES['KR_BANNER']['size'] < 5 * 1024 * 1024;
            $pathInfo = pathinfo($fileName);
            $ext = strtolower($pathInfo['extension']);

            if ($sizeOK && in_array($ext, $extPerm)) {
                $bannerName = "banner_{$newId}.{$ext}";
                $destB = __DIR__ . "/../Imagens/banners/{$bannerName}";

                foreach ($extPerm as $e) {
                    $oldB = __DIR__ . "/../Imagens/banners/banner_{$newId}.{$e}";
                    if (file_exists($oldB))
                        unlink($oldB);
                }

                move_uploaded_file($fileTmp, $destB);

                $updB = $mysqli->prepare("UPDATE KR_USERS SET KR_BANNER = ? WHERE ID = ?");
                $updB->bind_param('si', $bannerName, $newId);
                $updB->execute();
                $updB->close();
            }
        }

        header('Location: users-adm.php?msg=added_user');
        exit;
    }

    // -------------------------------------------------
    // B) EDITAR UTILIZADOR
    // -------------------------------------------------
    if ($action === 'edit_user') {
        $id = intval($_POST['ID']);
        $email = trim($_POST['KR_EMAIL_USER'] ?? '');
        $username = trim($_POST['KR_USER_NAME'] ?? '');
        $name = trim($_POST['KR_NAME'] ?? '');
        $country = trim($_POST['KR_COUNTRY'] ?? '');
        $city = trim($_POST['KR_CITY'] ?? '');
        $genero = $_POST['KR_GENERO'] ?? 'Prefiro não divulgar';
        $robloxUsername = trim($_POST['ROBLOX_USERNAME'] ?? '');

        // Só SUPERADM pode alterar o role
        $role = ($_SESSION['role'] === 'SUPERADM' && isset($_POST['KR_ROLE']))
            ? $_POST['KR_ROLE']
            : null;

        // Redes sociais - converter valores vazios para NULL
        $emailSocial = trim($_POST['EMAIL_SOCIAL'] ?? '') ?: null;
        $whatsapp = trim($_POST['WHATSAPP'] ?? '') ?: null;
        $youtube = trim($_POST['YOUTUBE'] ?? '') ?: null;
        $instagram = trim($_POST['INSTAGRAM'] ?? '') ?: null;
        $tiktok = trim($_POST['TIKTOK'] ?? '') ?: null;
        $facebook = trim($_POST['FACEBOOK'] ?? '') ?: null;
        $linkedin = trim($_POST['LINKEDIN'] ?? '') ?: null;
        $github = trim($_POST['GITHUB'] ?? '') ?: null;
        $twitter = trim($_POST['TWITTER'] ?? '') ?: null;

        // 1) Validações básicas
        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !preg_match(
                '/^[A-Za-z0-9._]{3,30}$/'
                ,
                $username
            )
        ) {
            header('Location: users-adm.php?msg=error_invalid');
            exit;
        }

        // 2) Selecionar avatar e banner atuais
        $stmtOld = $mysqli->prepare("SELECT KR_AVATAR, KR_BANNER FROM KR_USERS WHERE ID = ?");
        $stmtOld->bind_param('i', $id);
        $stmtOld->execute();
        $resOld = $stmtOld->get_result();
        $rowOld = $resOld->fetch_assoc();
        $oldAvatar = $rowOld['KR_AVATAR'] ?? null;
        $oldBanner = $rowOld['KR_BANNER'] ?? null;
        $stmtOld->close();

        // 3) Atualizar campos do utilizador
        if ($role !== null) {
            $stmtUpd = $mysqli->prepare("
              UPDATE KR_USERS
              SET KR_EMAIL_USER = ?, KR_USER_NAME = ?, KR_NAME = ?, KR_COUNTRY = ?, 
                  KR_CITY = ?, KR_GENERO = ?, KR_ROLE = ?, ROBLOX_USERNAME = ?
              WHERE ID = ?
            ");
            $stmtUpd->bind_param(
                'ssssssssi',
                $email,
                $username,
                $name,
                $country,
                $city,
                $genero,
                $role,
                $robloxUsername,
                $id
            );
        } else {
            $stmtUpd = $mysqli->prepare("
              UPDATE KR_USERS
              SET KR_EMAIL_USER = ?, KR_USER_NAME = ?, KR_NAME = ?, KR_COUNTRY = ?, 
                  KR_CITY = ?, KR_GENERO = ?, ROBLOX_USERNAME = ?
              WHERE ID = ?
            ");
            $stmtUpd->bind_param(
                'sssssssi',
                $email,
                $username,
                $name,
                $country,
                $city,
                $genero,
                $robloxUsername,
                $id
            );
        }
        $stmtUpd->execute();
        $stmtUpd->close();

        // 4) Atualizar redes sociais - CORRIGIDO
        $stmtSocialsUpd = $mysqli->prepare("
            UPDATE KR_USER_SOCIALS
            SET EMAIL_SOCIAL = ?, WHATSAPP = ?, YOUTUBE = ?, INSTAGRAM = ?, 
                TIKTOK = ?, FACEBOOK = ?, LINKEDIN = ?, GITHUB = ?, TWITTER = ?
            WHERE SOCIAL_ID = ?
        ");
        $stmtSocialsUpd->bind_param(
            'sssssssssi',
            $emailSocial,
            $whatsapp,
            $youtube,
            $instagram,
            $tiktok,
            $facebook,
            $linkedin,
            $github,
            $twitter,
            $id
        );
        $stmtSocialsUpd->execute();
        $stmtSocialsUpd->close();

        // 5) Processar uploads de ficheiros (avatar e banner)
        // Avatar
        if (isset($_FILES['KR_AVATAR']) && $_FILES['KR_AVATAR']['error'] === UPLOAD_ERR_OK) {
            $extPerm = ['jpg', 'jpeg', 'png', 'gif'];
            $fileTmp = $_FILES['KR_AVATAR']['tmp_name'];
            $fileName = $_FILES['KR_AVATAR']['name'];
            $sizeOK = $_FILES['KR_AVATAR']['size'] > 0 && $_FILES['KR_AVATAR']['size'] < 5 * 1024 * 1024;
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($sizeOK && in_array($ext, $extPerm)) {
                $avatarName = "avatar_{$id}.{$ext}";
                $destA = __DIR__ . "/../Imagens/avatares/{$avatarName}";

                apagarFicheiroAnterior($oldAvatar, 'avatar');
                move_uploaded_file($fileTmp, $destA);

                $updA = $mysqli->prepare("UPDATE KR_USERS SET KR_AVATAR = ? WHERE ID = ?");
                $updA->bind_param('si', $avatarName, $id);
                $updA->execute();
                $updA->close();
            }
        }

        // Banner
        if (isset($_FILES['KR_BANNER']) && $_FILES['KR_BANNER']['error'] === UPLOAD_ERR_OK) {
            $extPerm = ['jpg', 'jpeg', 'png', 'gif'];
            $fileTmp = $_FILES['KR_BANNER']['tmp_name'];
            $fileName = $_FILES['KR_BANNER']['name'];
            $sizeOK = $_FILES['KR_BANNER']['size'] > 0 && $_FILES['KR_BANNER']['size'] < 5 * 1024 * 1024;
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($sizeOK && in_array($ext, $extPerm)) {
                $bannerName = "banner_{$id}.{$ext}";
                $destB = __DIR__ . "/../Imagens/banners/{$bannerName}";

                apagarFicheiroAnterior($oldBanner, 'banner');
                move_uploaded_file($fileTmp, $destB);

                $updB = $mysqli->prepare("UPDATE KR_USERS SET KR_BANNER = ? WHERE ID = ?");
                $updB->bind_param('si', $bannerName, $id);
                $updB->execute();
                $updB->close();
            }
        }

        header('Location: users-adm.php?msg=edited_user');
        exit;
    }
}

// ==============================
// LER TODOS OS UTILIZADORES para exibição
// ==============================
$users = [];
$resultAll = $mysqli->query("
  SELECT
    u.ID,
    u.KR_EMAIL_USER,
    u.KR_USER_NAME,
    u.KR_NAME,
    u.KR_COUNTRY,
    u.KR_CITY,
    u.KR_GENERO,
    u.KR_ROLE,
    u.KR_CREATED_AT,
    u.KR_LAST_LOGIN,
    u.KR_AVATAR,
    u.KR_BANNER,
    u.ROBLOX_USERNAME,
    s.EMAIL_SOCIAL,
    s.WHATSAPP,
    s.YOUTUBE,
    s.INSTAGRAM,
    s.TIKTOK,
    s.FACEBOOK,
    s.LINKEDIN,
    s.GITHUB,
    s.TWITTER
  FROM KR_USERS u
  LEFT JOIN KR_USER_SOCIALS s ON u.ID = s.SOCIAL_ID
  ORDER BY u.ID ASC
");
if ($resultAll) {
    while ($row = $resultAll->fetch_assoc()) {
        $users[] = $row;
    }
    $resultAll->free();
}

// ==============================
// Mensagem de Feedback
// ==============================
$msgFeedback = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'deleted':
            $msgFeedback = '<div class="uadm-feedback uadm-feedback-success">Utilizador eliminado com sucesso.</div>';
            break;
        case 'added_user':
            $msgFeedback = '<div class="uadm-feedback uadm-feedback-success">Utilizador adicionado com sucesso.</div>';
            break;
        case 'edited_user':
            $msgFeedback = '<div class="uadm-feedback uadm-feedback-success">Utilizador editado com sucesso.</div>';
            break;
        case 'error_invalid':
            $msgFeedback = '<div class="uadm-feedback uadm-feedback-error">Email ou Username inválido.</div>';
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <!-- Favicon padrão (navegadores desktop) -->
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="shortcut icon" href="favicon_io/favicon.ico">

    <!-- Favicon para Android -->
    <link rel="icon" type="image/png" sizes="192x192" href="../favicon_io/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../favicon_io/android-chrome-512x512.png">

    <!-- Favicon para iOS (iPhone, iPad) -->
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">

    <!-- Manifesto para apps web em Android -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#000000">

    <title>KR Legends - Gerir Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../css/Geral.css" rel="stylesheet">
    <link href="../css/cookieConsent.css" rel="stylesheet">
    <link href="../css/includes/profileMenu.css" rel="stylesheet">
    <link href="../css/includes/modal-logout.css" rel="stylesheet">
    <link href="CSS/geral-adm.css" rel="stylesheet">
    <link href="CSS/users-adm.css" rel="stylesheet">
    <link href="CSS/users-adm-responsive.css" rel="stylesheet">
</head>

<body>

    <!-- ========== MINI CARRINHO (SLIDE) ========== -->
    <div class="mini-cart" id="miniCart">
        <div class="mini-cart-header">
            <h3>Carrinho de Compras</h3>
            <button class="mini-cart-close" id="closeMiniCart">
                <!-- Ícone de fechar (SVG) -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="mini-cart-items" id="miniCartItems">
            <!-- Produtos adicionados serão injetados dinamicamente aqui -->
        </div>
        <div class="mini-cart-footer">
            <div class="mini-cart-subtotal">
                <span>Subtotal:</span>
                <span id="cartSubtotal">€0.00</span>
            </div>
            <button class="btn btn-primary btn-block">Finalizar Compra</button>
        </div>
    </div>

    <!-- Modal Confirmar Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog logout-modal-dialog">
            <div class="modal-content logout-modal-content logout-modal-border">
                <div class="modal-header logout-modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirmar Logout</h5>
                    <button type="button" class="btn-close btn-close-white logout-btn-close-white"
                        data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body logout-modal-body">
                    Tem certeza de que deseja terminar a sessão?
                </div>
                <div class="modal-footer logout-modal-footer">
                    <!-- Botão Cancelar -->
                    <button type="button" class="btn btn-outline-light logout-btn-cancel"
                        data-bs-dismiss="modal">Cancelar</button>

                    <!-- Formulário para confirmar o logout -->
                    <form action="../PHP/logout.php" method="POST" style="display:inline-block;">
                        <button type="submit" class="btn logout-btn-confirm-logout">Terminar Sessão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Sobreposição visual quando o mini carrinho estiver ativo -->
    <div class="overlay" id="overlay"></div>
    <!-- Header -->
    <header class="fixed-top bg-black bg-opacity-90 border-bottom border-warning border-opacity-25">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" class="logo" href="../Index.php">
                    <img src="../Imagens/Logotipos/Logo KR Legends - soft trans recortado.png" alt="KR Legends"
                        height="70">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">

                    <ul class="navbar-nav me-auto">

                        <!-- Sobre Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="sobreDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-info-circle me-1" aria-hidden="true"></i> Sobre
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="sobreDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Index.php">
                                        <i class="bi bi-house-door me-2" aria-hidden="true"></i> Início
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../PAP.php">
                                        <i class="bi bi-journal-text me-2" aria-hidden="true"></i> PAP
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Equipa.php">
                                        <i class="bi bi-people me-2" aria-hidden="true"></i> Equipa
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Creditos.php">
                                        <i class="bi bi-award me-2" aria-hidden="true"></i> Créditos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Politica.php">
                                        <i class="fas fa-vote-yea me-2" aria-hidden="true"></i> Politica
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Comunidade & Social Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                                id="comSocialDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-people-fill me-1" aria-hidden="true"></i> Comunidade & Social
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="comSocialDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Comunidade.php">
                                        <i class="bi bi-people me-2" aria-hidden="true"></i> Comunidade
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Suporte.php">
                                        <i class="bi bi-life-preserver me-2" aria-hidden="true"></i> Suporte
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider bg-secondary" />
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="https://www.instagram.com/kr_legends.oficial/" target="_blank">
                                        <i class="bi bi-instagram me-2" aria-hidden="true"></i> Instagram
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="#" target="_blank">
                                        <i class="bi bi-discord me-2" aria-hidden="true"></i> Discord
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="https://www.tiktok.com/@kr.legends?lang=pt-BR" target="_blank">
                                        <i class="bi bi-tiktok me-2" aria-hidden="true"></i> TikTok
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="https://www.youtube.com/@KRLegends-media" target="_blank">
                                        <i class="bi bi-youtube me-2" aria-hidden="true"></i> YouTube
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Jogo Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="jogoDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-controller me-1" aria-hidden="true"></i> Jogo
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="jogoDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Atualizacoes.php">
                                        <i class="bi bi-bell me-2" aria-hidden="true"></i> Atualizações
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../modos-de-jogo.php">
                                        <i class="bi bi-joystick me-2" aria-hidden="true"></i> Modos de Jogo
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Rankings.php">
                                        <i class="bi bi-bar-chart-line me-2" aria-hidden="true"></i> Rankings
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Galeria.php">
                                        <i class="bi bi-images me-2" aria-hidden="true"></i> Galeria
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Loja -->
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="../Store.php">
                                <i class="bi bi-shop me-1" aria-hidden="true"></i> Loja Online
                            </a>
                        </li>
                    </ul>

                    <div class="language-selector">
                        <select id="languageSelector" class="language-select" onchange="setLanguage(this.value)">
                            <option value="pt">🇵🇹 Português</option>
                            <option value="en">🇬🇧 English</option>
                            <option value="es">🇪🇸 Español</option>
                            <option value="fr">🇫🇷 Français</option>
                        </select>
                    </div>

                    <!-- Botões de autenticação e carrinho -->
                    <div class="auth-buttons">
                        <div class="profile-menu-container">
                            <!-- User profile name with link to profile page -->
                            <a href="../Utilizador/perfil.php" class="profile-name" title="Ver perfil">
                                <?php
                                // Verifique se o nome completo existe, caso contrário mostre o nome de usuário
                                $displayName = !empty($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['user_name'];
                                echo htmlspecialchars($displayName);
                                ?>
                            </a>

                            <!-- Avatar dropdown trigger -->
                            <div class="dropdown">
                                <a class="profile-avatar-trigger" href="#" id="userMenu" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false" title="Menu do utilizador">
                                    <img src="../Imagens/avatares/<?= htmlspecialchars($_SESSION['user_avatar']) ?>"
                                        alt="Avatar do utilizador" class="rounded-circle">
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end"
                                    aria-labelledby="userMenu">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="../Utilizador/perfil.php">
                                            <i class="bi bi-person-circle me-2"></i> Perfil
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center"
                                            href="../Utilizador/stats.php">
                                            <i class="bi bi-bar-chart-line me-2"></i> Estatísticas
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center"
                                            href="../Utilizador/settings.php">
                                            <i class="bi bi-gear me-2"></i> Definições
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="../Suporte.php">
                                            <i class="bi bi-question-circle me-2"></i> Ajuda & FAQ
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="../Politica.php">
                                            <i class="bi bi-file-earmark-text me-2"></i> Termos & Políticas
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center text-danger"
                                            data-bs-toggle="modal" data-bs-target="#logoutModal" id="terminar">
                                            <i class="bi bi-box-arrow-right me-2"></i> Terminar Sessão
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <!-- Ícone Administrativo (ADM ou SUPERADM) -->
                            <?php
                            if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['ADM', 'SUPERADM'], true)):
                                if ($_SESSION['role'] === 'SUPERADM') {
                                    // Usa ícone de “gema” para SUPERADM (fa-solid fa-gem)
                                    $iconClass = 'fa-solid fa-gem';
                                    $ariaLabel = 'Área Super Admin';
                                } else {
                                    // Usa ícone de “pessoa com gravata” para ADM (fa-solid fa-user-tie)
                                    $iconClass = 'fa-solid fa-user-tie';
                                    $ariaLabel = 'Área Admin';
                                }
                                // Link placeholder para a página administrativa
                                $adminUrl = 'modoADM.php';
                                ?>
                                <a href="<?= htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8') ?>" class="profile-admin-icon"
                                    title="<?= htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') ?>"
                                    aria-label="<?= htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="<?= $iconClass ?>" aria-hidden="true"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <button class="cart-button" id="openMiniCart">
                            <div class="cart-icon-wrapper">
                                <i class="bi bi-cart3"></i>
                                <span class="cart-count" id="cartCount">0</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <!-- Overlay para sidebar em dispositivos móveis -->
    <div class="adm-sidebar-overlay" id="sidebarOverlay"></div>
    <div id="page">
        <!-- INÍCIO DO CABEÇALHO LATERAL (SIDEBAR) -->
        <aside class="adm-sidebar" id="adminSidebar">
            <div class="adm-sidebar-header">
                <i class="bi bi-gear-fill me-2"></i>
                <a href="modoADM.php" class="adm-sidebar-header-a">Modo ADM</a>
            </div>

            <ul class="adm-sidebar-nav">
                <!-- Seção: Páginas -->
                <li class="adm-sidebar-section">
                    <div class="adm-sidebar-section-name">Páginas</div>
                    <a href="equipa-adm.php" class="adm-sidebar-item">
                        <i class="bi bi-people-fill"></i>
                        Equipa
                    </a>
                    <a href="#" class="adm-sidebar-item">
                        <i class="bi bi-bookmarks"></i>
                        Histórico da Política
                    </a>
                    <a href="#" class="adm-sidebar-item">
                        <i class="bi bi-arrow-repeat"></i>
                        Atualizações
                    </a>
                    <a href="#" class="adm-sidebar-item">
                        <i class="bi bi-controller"></i>
                        Modos de Jogo
                    </a>
                    <a href="#" class="adm-sidebar-item">
                        <i class="bi bi-award"></i>
                        Rankings
                    </a>
                    <a href="galeria-adm.php" class="adm-sidebar-item">
                        <i class="bi bi-images"></i>
                        Galeria
                    </a>
                </li>

                <!-- Seção: Loja online -->
                <li class="adm-sidebar-section">
                    <div class="adm-sidebar-section-name">Loja online</div>
                    <a href="#" class="adm-sidebar-item">
                        <i class="bi bi-cart"></i>
                        Produtos
                    </a>
                </li>

                <!-- Seção: Utilizadores -->
                <li class="adm-sidebar-section">
                    <div class="adm-sidebar-section-name">Utilizadores</div>
                    <a href="#" class="adm-sidebar-item disabled">
                        <i class="bi bi-person-lines-fill"></i>
                        Utilizadores
                    </a>
                    <a href="#" class="adm-sidebar-item">
                        <i class="bi bi-person-x"></i>
                        Banimentos
                    </a>
                </li>

                <!-- Seção: Suporte & Apoio ao Cliente -->
                <li class="adm-sidebar-section">
                    <div class="adm-sidebar-section-name">Apoio ao Cliente</div>
                    <a href="#" class="adm-sidebar-item">
                        <i class="bi bi-chat-dots"></i>
                        FAQ da Comunidade
                    </a>
                    <a href="suporte-adm.php" class="adm-sidebar-item">
                        <i class="bi bi-question-diamond"></i>
                        Pedidos de Suporte
                    </a>
                </li>

                <!-- Seção: Logs -->
                <li class="adm-sidebar-section">
                    <div class="adm-sidebar-section-name">Logs</div>
                    <a href="#" class="adm-sidebar-item">
                        <i class="bi bi-journal-text"></i>
                        Logs
                    </a>
                </li>
            </ul>
        </aside>

        <main class="uadm-main">
            <div class="uadm-header">
                <h1 class="uadm-title">Gestão de Utilizadores</h1>
                <p class="uadm-subtitle">
                    Aqui podes consultar, adicionar, editar ou eliminar os utilizadores registados na plataforma.
                </p>
            </div>

            <!-- Botão "Novo Utilizador" -->
            <div class="uadm-actions-bar">
                <div class="uadm-actions-left">
                    <button type="button" class="uadm-btn uadm-btn-primary" onclick="openAddUserModal()">
                        <i class="bi bi-plus-lg"></i> Novo Utilizador
                    </button>
                </div>
                <div class="uadm-actions-right">
                    <button type="button" id="toggleUserFilters" class="uadm-btn uadm-btn-secondary"
                        title="Mostrar filtros">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </div>

            <!-- Mensagem de Feedback -->
            <?= $msgFeedback ?>

            <!-- ========== Pesquisa ========== -->
            <div class="uadm-search-container">
                <i class="uadm-search-icon bi bi-search"></i>
                <input type="text" id="searchUserInput" class="uadm-search-input"
                    placeholder="Pesquisar por ID, Email, Username, País ou Cidade..." />
            </div>

            <!-- ========== Painel de Filtros ========= -->
            <div id="userFilterPanel" class="uadm-filters-panel" style="display: none;">
                <div class="uadm-filters-grid">
                    <!-- Ordenar por ID -->
                    <div class="uadm-filter-group">
                        <label for="filterUserOrder" class="uadm-filter-label">Ordenar ID:</label>
                        <select id="filterUserOrder" class="uadm-filter-select">
                            <option value="asc">Crescente</option>
                            <option value="desc">Decrescente</option>
                        </select>
                    </div>

                    <!-- Filtrar por Role -->
                    <div class="uadm-filter-group">
                        <label for="filterRoleUser" class="uadm-filter-label">Role:</label>
                        <select id="filterRoleUser" class="uadm-filter-select">
                            <option value="">Todos</option>
                            <option value="user">USER</option>
                            <option value="adm">ADM</option>
                            <option value="superadm">SUPERADM</option>
                        </select>
                    </div>

                    <!-- Filtrar por Gênero -->
                    <div class="uadm-filter-group">
                        <label for="filterGenero" class="uadm-filter-label">Gênero:</label>
                        <select id="filterGenero" class="uadm-filter-select">
                            <option value="">Todos</option>
                            <option value="masculino">Masculino</option>
                            <option value="feminino">Feminino</option>
                            <option value="prefiro não divulgar">Prefiro não divulgar</option>
                        </select>
                    </div>

                    <!-- Extensão do Avatar -->
                    <div class="uadm-filter-group">
                        <label for="filterAvatarExt" class="uadm-filter-label">Avatar Ext.:</label>
                        <select id="filterAvatarExt" class="uadm-filter-select">
                            <option value="">Todas</option>
                            <option value="jpg">.jpg</option>
                            <option value="jpeg">.jpeg</option>
                            <option value="png">.png</option>
                            <option value="gif">.gif</option>
                        </select>
                    </div>

                    <!-- Extensão do Banner -->
                    <div class="uadm-filter-group">
                        <label for="filterBannerExt" class="uadm-filter-label">Banner Ext.:</label>
                        <select id="filterBannerExt" class="uadm-filter-select">
                            <option value="">Todas</option>
                            <option value="jpg">.jpg</option>
                            <option value="jpeg">.jpeg</option>
                            <option value="png">.png</option>
                            <option value="gif">.gif</option>
                        </select>
                    </div>

                    <!-- Contas mais antigas -->
                    <div class="uadm-filter-group">
                        <label for="filterCreated" class="uadm-filter-label">Ordenar por Data:</label>
                        <select id="filterCreated" class="uadm-filter-select">
                            <option value="new">Mais recentes</option>
                            <option value="old">Mais antigas</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ========== Estatísticas ========== -->
            <div class="uadm-stats">
                <div class="uadm-stat-card">
                    <div class="uadm-stat-number"><?= count($users) ?></div>
                    <div class="uadm-stat-label">Total de Utilizadores</div>
                </div>
                <div class="uadm-stat-card">
                    <div class="uadm-stat-number" id="filteredCount"><?= count($users) ?></div>
                    <div class="uadm-stat-label">Utilizadores Filtrados</div>
                </div>
                <div class="uadm-stat-card">
                    <div class="uadm-stat-number">
                        <?= count(array_filter($users, fn($u) => $u['KR_ROLE'] === 'ADM' || $u['KR_ROLE'] === 'SUPERADM')) ?>
                    </div>
                    <div class="uadm-stat-label">Administradores</div>
                </div>
                <div class="uadm-stat-card">
                    <div class="uadm-stat-number">
                        <?= count(array_filter($users, fn($u) => !str_contains($u['KR_AVATAR'], 'avatar_default'))) ?>
                    </div>
                    <div class="uadm-stat-label">Com Avatar Personalizado</div>
                </div>
            </div>

            <!-- ========== TABELA DE UTILIZADORES ========= -->
            <div class="uadm-table-container">
                <table class="uadm-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Avatar</th>
                            <th>Banner</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Nome</th>
                            <th>País</th>
                            <th>Cidade</th>
                            <th>Gênero</th>
                            <th>Role</th>
                            <th>Roblox</th>
                            <th>Criado em</th>
                            <th>Último Login</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="14" class="uadm-text-center"
                                    style="padding: 2rem; color: var(--color-gray-400);">
                                    <i class="bi bi-search"
                                        style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                    Nenhum utilizador encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($users as $u): ?>
                            <?php
                            // Extensão do avatar/banners
                            $avatarExt = '';
                            if (!empty($u['KR_AVATAR'])) {
                                $avatarExt = strtolower(pathinfo($u['KR_AVATAR'], PATHINFO_EXTENSION));
                            }
                            $bannerExt = '';
                            if (!empty($u['KR_BANNER'])) {
                                $bannerExt = strtolower(pathinfo($u['KR_BANNER'], PATHINFO_EXTENSION));
                            }
                            ?>
                            <tr data-avatar-ext="<?= $avatarExt ?>" data-banner-ext="<?= $bannerExt ?>"
                                data-genero="<?= strtolower(htmlspecialchars($u['KR_GENERO'])) ?>"
                                data-role="<?= strtolower(htmlspecialchars($u['KR_ROLE'])) ?>">
                                <td class="cell-id"><?= htmlspecialchars($u['ID']) ?></td>
                                <td style="text-align:center;">
                                    <img src="../Imagens/avatares/<?= htmlspecialchars($u['KR_AVATAR']) ?>"
                                        alt="Avatar <?= $u['ID'] ?>" class="uadm-avatar" />
                                </td>
                                <td style="text-align:center;">
                                    <img src="../Imagens/banners/<?= htmlspecialchars($u['KR_BANNER']) ?>"
                                        alt="Banner <?= $u['ID'] ?>" class="uadm-banner-thumb" />
                                </td>
                                <td class="cell-email uadm-text-truncate"><?= htmlspecialchars($u['KR_EMAIL_USER']) ?></td>
                                <td class="cell-username"><?= htmlspecialchars($u['KR_USER_NAME']) ?></td>
                                <td class="uadm-text-truncate"><?= htmlspecialchars($u['KR_NAME'] ?? '') ?></td>
                                <td class="cell-country"><?= htmlspecialchars($u['KR_COUNTRY']) ?></td>
                                <td class="cell-city"><?= htmlspecialchars($u['KR_CITY'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['KR_GENERO']) ?></td>
                                <td>
                                    <span class="uadm-role-badge uadm-role-<?= strtolower($u['KR_ROLE']) ?>">
                                        <?= htmlspecialchars($u['KR_ROLE']) ?>
                                    </span>
                                </td>
                                <td class="uadm-text-truncate"><?= htmlspecialchars($u['ROBLOX_USERNAME'] ?? '') ?></td>
                                <td class="cell-created"><?= date('d/m/Y H:i', strtotime($u['KR_CREATED_AT'])) ?></td>
                                <td><?= $u['KR_LAST_LOGIN'] ? date('d/m/Y H:i', strtotime($u['KR_LAST_LOGIN'])) : '—' ?>
                                </td>
                                <td class="uadm-table-actions">
                                    <!-- Editar -->
                                    <button class="uadm-btn uadm-btn-sm uadm-btn-secondary" onclick="openEditUserModal(
                    <?= $u['ID'] ?>,
                    '<?= addslashes($u['KR_EMAIL_USER']) ?>',
                    '<?= addslashes($u['KR_USER_NAME']) ?>',
                    '<?= addslashes($u['KR_NAME'] ?? '') ?>',
                    '<?= addslashes($u['KR_COUNTRY']) ?>',
                    '<?= addslashes($u['KR_CITY'] ?? '') ?>',
                    '<?= addslashes($u['KR_GENERO']) ?>',
                    '<?= addslashes($u['KR_ROLE']) ?>',
                    '<?= addslashes($u['ROBLOX_USERNAME'] ?? '') ?>',
                    '<?= addslashes($u['EMAIL_SOCIAL'] ?? '') ?>',
                    '<?= addslashes($u['WHATSAPP'] ?? '') ?>',
                    '<?= addslashes($u['YOUTUBE'] ?? '') ?>',
                    '<?= addslashes($u['INSTAGRAM'] ?? '') ?>',
                    '<?= addslashes($u['TIKTOK'] ?? '') ?>',
                    '<?= addslashes($u['FACEBOOK'] ?? '') ?>',
                    '<?= addslashes($u['LINKEDIN'] ?? '') ?>',
                    '<?= addslashes($u['GITHUB'] ?? '') ?>',
                    '<?= addslashes($u['TWITTER'] ?? '') ?>'
                  )" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <!-- Eliminar -->
                                    <a href="users-adm.php?delete_id=<?= $u['ID'] ?>"
                                        class="uadm-btn uadm-btn-sm uadm-btn-danger"
                                        onclick="return confirm('Quer mesmo eliminar este utilizador?');" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <!-- ========== MODAIS (Adicionar / Editar) ========== -->
        <!-- Adicionar Utilizador -->
        <div id="modalAddUser" class="uadm-modal-backdrop" style="display:none;">
            <div class="uadm-modal-content">
                <h2>Adicionar Novo Utilizador</h2>
                <form action="users-adm.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_user" />

                    <div class="uadm-form-grid">
                        <div class="uadm-form-group">
                            <label for="new_email">Email*:</label>
                            <input type="email" name="KR_EMAIL_USER" id="new_email" required />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_username">Username*:</label>
                            <input type="text" name="KR_USER_NAME" id="new_username" pattern="^[A-Za-z0-9._]{3,30}$"
                                required title="Apenas letras, números e pontos." />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_name">Nome Completo:</label>
                            <input type="text" name="KR_NAME" id="new_name" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_password">Password*:</label>
                            <input type="password" name="KR_PASSWORD" id="new_password" required minlength="6" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_country">País:</label>
                            <input type="text" name="KR_COUNTRY" id="new_country" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_city">Cidade:</label>
                            <input type="text" name="KR_CITY" id="new_city" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_genero">Gênero:</label>
                            <select name="KR_GENERO" id="new_genero">
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                                <option value="Prefiro não divulgar" selected>Prefiro não divulgar</option>
                            </select>
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_roblox">Username Roblox:</label>
                            <input type="text" name="ROBLOX_USERNAME" id="new_roblox" maxlength="21" />
                        </div>
                        <?php if ($_SESSION['role'] === 'SUPERADM'): ?>
                            <div class="uadm-form-group">
                                <label for="new_role">Role:</label>
                                <select name="KR_ROLE" id="new_role">
                                    <option value="USER" selected>USER</option>
                                    <option value="ADM">ADM</option>
                                    <option value="SUPERADM">SUPERADM</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Redes Sociais -->
                    <h3 style="color: var(--color-yellow); margin: 1.5rem 0 1rem 0; font-size: 1.2rem;">Redes Sociais
                    </h3>
                    <div class="uadm-form-grid">
                        <div class="uadm-form-group">
                            <label for="new_email_social">Email Social:</label>
                            <input type="email" name="EMAIL_SOCIAL" id="new_email_social" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_whatsapp">WhatsApp:</label>
                            <input type="text" name="WHATSAPP" id="new_whatsapp" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_youtube">YouTube:</label>
                            <input type="url" name="YOUTUBE" id="new_youtube" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_instagram">Instagram:</label>
                            <input type="url" name="INSTAGRAM" id="new_instagram" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_tiktok">TikTok:</label>
                            <input type="url" name="TIKTOK" id="new_tiktok" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_facebook">Facebook:</label>
                            <input type="url" name="FACEBOOK" id="new_facebook" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_linkedin">LinkedIn:</label>
                            <input type="url" name="LINKEDIN" id="new_linkedin" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_github">GitHub:</label>
                            <input type="url" name="GITHUB" id="new_github" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_twitter">Twitter:</label>
                            <input type="url" name="TWITTER" id="new_twitter" />
                        </div>
                    </div>

                    <!-- Ficheiros -->
                    <h3 style="color: var(--color-yellow); margin: 1.5rem 0 1rem 0; font-size: 1.2rem;">Ficheiros</h3>
                    <div class="uadm-form-grid">
                        <div class="uadm-form-group">
                            <label for="new_avatar">Avatar (jpg/png/gif):</label>
                            <input type="file" name="KR_AVATAR" id="new_avatar" accept=".jpg,.jpeg,.png,.gif" />
                            <small>Máximo 5MB</small>
                        </div>
                        <div class="uadm-form-group">
                            <label for="new_banner">Banner (jpg/png/gif):</label>
                            <input type="file" name="KR_BANNER" id="new_banner" accept=".jpg,.jpeg,.png,.gif" />
                            <small>Máximo 5MB</small>
                        </div>
                    </div>

                    <div class="uadm-modal-actions">
                        <button type="submit" class="uadm-btn uadm-btn-primary">Guardar</button>
                        <button type="button" class="uadm-btn uadm-btn-secondary" onclick="closeAddUserModal()">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Editar Utilizador -->
        <div id="modalEditUser" class="uadm-modal-backdrop" style="display:none;">
            <div class="uadm-modal-content">
                <h2>Editar Utilizador</h2>
                <form action="users-adm.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit_user" />
                    <input type="hidden" name="ID" id="edit_user_id" value="" />

                    <div class="uadm-form-grid">
                        <div class="uadm-form-group">
                            <label for="edit_email">Email*:</label>
                            <input type="email" name="KR_EMAIL_USER" id="edit_email" required />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_username">Username*:</label>
                            <input type="text" name="KR_USER_NAME" id="edit_username" pattern="^[A-Za-z0-9._]{3,30}$"
                                required title="Apenas letras, números e pontos." />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_name">Nome Completo:</label>
                            <input type="text" name="KR_NAME" id="edit_name" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_country">País:</label>
                            <input type="text" name="KR_COUNTRY" id="edit_country" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_city">Cidade:</label>
                            <input type="text" name="KR_CITY" id="edit_city" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_genero">Gênero:</label>
                            <select name="KR_GENERO" id="edit_genero">
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                                <option value="Prefiro não divulgar">Prefiro não divulgar</option>
                            </select>
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_roblox">Username Roblox:</label>
                            <input type="text" name="ROBLOX_USERNAME" id="edit_roblox" maxlength="21" />
                        </div>
                        <?php if ($_SESSION['role'] === 'SUPERADM'): ?>
                            <div class="uadm-form-group">
                                <label for="edit_role">Role:</label>
                                <select name="KR_ROLE" id="edit_role">
                                    <option value="USER">USER</option>
                                    <option value="ADM">ADM</option>
                                    <option value="SUPERADM">SUPERADM</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Redes Sociais -->
                    <h3 style="color: var(--color-yellow); margin: 1.5rem 0 1rem 0; font-size: 1.2rem;">Redes Sociais
                    </h3>
                    <div class="uadm-form-grid">
                        <div class="uadm-form-group">
                            <label for="edit_email_social">Email Social:</label>
                            <input type="email" name="EMAIL_SOCIAL" id="edit_email_social" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_whatsapp">WhatsApp:</label>
                            <input type="text" name="WHATSAPP" id="edit_whatsapp" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_youtube">YouTube:</label>
                            <input type="url" name="YOUTUBE" id="edit_youtube" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_instagram">Instagram:</label>
                            <input type="url" name="INSTAGRAM" id="edit_instagram" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_tiktok">TikTok:</label>
                            <input type="url" name="TIKTOK" id="edit_tiktok" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_facebook">Facebook:</label>
                            <input type="url" name="FACEBOOK" id="edit_facebook" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_linkedin">LinkedIn:</label>
                            <input type="url" name="LINKEDIN" id="edit_linkedin" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_github">GitHub:</label>
                            <input type="url" name="GITHUB" id="edit_github" />
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_twitter">Twitter:</label>
                            <input type="url" name="TWITTER" id="edit_twitter" />
                        </div>
                    </div>

                    <!-- Ficheiros -->
                    <h3 style="color: var(--color-yellow); margin: 1.5rem 0 1rem 0; font-size: 1.2rem;">Ficheiros</h3>
                    <div class="uadm-form-grid">
                        <div class="uadm-form-group">
                            <label for="edit_avatar">Avatar (substituir):</label>
                            <input type="file" name="KR_AVATAR" id="edit_avatar" accept=".jpg,.jpeg,.png,.gif" />
                            <small>Se não escolher nada, mantém o avatar atual</small>
                        </div>
                        <div class="uadm-form-group">
                            <label for="edit_banner">Banner (substituir):</label>
                            <input type="file" name="KR_BANNER" id="edit_banner" accept=".jpg,.jpeg,.png,.gif" />
                            <small>Se não escolher nada, mantém o banner atual</small>
                        </div>
                    </div>

                    <div class="uadm-modal-actions">
                        <button type="submit" class="uadm-btn uadm-btn-primary">Guardar Alterações</button>
                        <button type="button" class="uadm-btn uadm-btn-secondary" onclick="closeEditUserModal()">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-black py-5 border-top border-warning border-opacity-25">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-6">
                    <img src="../Imagens/Logotipos/Logo KR Legends - soft trans recortado.png" alt="KR Legends"
                        height="65" class="mb-3">
                    <p class="text-white-50" data-translate="footer.joinCommunity">
                        Junte-se à comunidade KR Legends e faça parte desta revolução no mundo das corridas virtuais.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h3 class="text-warning mb-3" data-translate="footer.followUs">Siga-nos nas Redes Sociais</h3>
                    <div class="social-links">
                        <a href="https://www.instagram.com/kr_legends.oficial/" target="_blank" class="social-link">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com/@KRLegends-media" target="_blank" class="social-link">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="https://www.tiktok.com/@kr.legends?lang=pt-BR" target="_blank" class="social-link">
                            <i class="bi bi-tiktok"></i>
                        </a>
                        <a href="https://www.roblox.com/pt/games/113586179382037/KR-Legends-Beta" target="_blank"
                            class="social-link">
                            <i class="bi bi-controller"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="text-center border-top border-warning border-opacity-25 pt-4">
                <p class="text-white-50 mb-2" data-translate="footer.rights">©2026 KR Legends. Todos os direitos
                    reservados.</p>
                <p class="text-white-50 small" data-translate="footer.developed">
                    Desenvolvido pela equipa de KR Legends com paixão pelo automobilismo e programação.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="JS\users-adm.js"></script>
    <script src="../js/includes/profileMenu.js"></script>
    <script src="../js/Gerais/Geral.js"></script>
    <script src="../js/Gerais/cookieConsent.js"></script>
    <script src="../js/Gerais/notification_API.js" defer></script>
</body>

</html>