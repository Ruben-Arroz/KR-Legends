<?php
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

// 2) Verificar permissão
if (!in_array($_SESSION['role'], ['ADM', 'SUPERADM'])) {
    http_response_code(403);
    die('Acesso negado');
}

// Função auxiliar para apagar imagem anterior (se não for 'member_default.png')
function apagarImagemAnterior(string $filename)
{
    $default = 'member_default.png';
    if ($filename && $filename !== $default) {
        $caminho = __DIR__ . '/../Imagens/Equipa/' . $filename;
        if (file_exists($caminho)) {
            unlink($caminho);
        }
    }
}

// -----------------------------
// 3. REMOVER MEMBRO (GET ?delete_id=XX)
// -----------------------------
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delId = (int) $_GET['delete_id'];

    // 1) Obter nome do ficheiro antigo para apagar depois
    $stmtImg = $mysqli->prepare("SELECT EQ_IMAGEM FROM KR_EQ_MEMBROS WHERE EQ_ID = ?");
    $stmtImg->bind_param('i', $delId);
    $stmtImg->execute();
    $resImg = $stmtImg->get_result();
    $rowImg = $resImg->fetch_assoc();
    $oldFilename = $rowImg['EQ_IMAGEM'] ?? null;
    $stmtImg->close();

    // 2) Apagar o registo da BD
    $stmtDel = $mysqli->prepare("DELETE FROM KR_EQ_MEMBROS WHERE EQ_ID = ?");
    $stmtDel->bind_param('i', $delId);
    $stmtDel->execute();
    $stmtDel->close();

    // 3) Apagar ficheiro do servidor (se não for default)
    apagarImagemAnterior($oldFilename);

    header('Location: equipa-adm.php?msg=deleted');
    exit;
}

// -----------------------------
// 4. INSERIR NOVO MEMBRO (POST action = add)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'add')) {
    $nome = trim($_POST['eq_nome'] ?? '');
    $funcao = trim($_POST['eq_funcao'] ?? '');
    $bio = trim($_POST['eq_bio'] ?? '');
    $quote = trim($_POST['eq_quote'] ?? '');
    $linkedin = trim($_POST['eq_linkedin'] ?? '');
    $email = trim($_POST['eq_email'] ?? '');
    $instagram = trim($_POST['eq_instagram'] ?? '');
    $youtube = trim($_POST['eq_youtube'] ?? '');

    // Nome e Função são obrigatórios
    if ($nome === '' || $funcao === '') {
        header('Location: equipa-adm.php?msg=error_missing');
        exit;
    }

    // 1) Inserir registo sem imagem (para obter o novo ID)
    $stmtIns = $mysqli->prepare("
      INSERT INTO KR_EQ_MEMBROS
        (EQ_IMAGEM, EQ_NOME, EQ_FUNCAO, EQ_BIO, EQ_QUOTE, 
         EQ_LINKEDIN, EQ_EMAIL, EQ_INSTAGRAM, EQ_YOUTUBE)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $defaultImg = 'member_default.png';
    $bioVal = $bio !== '' ? $bio : null;
    $quoteVal = $quote !== '' ? $quote : null;
    $linkedinVal = $linkedin !== '' ? $linkedin : null;
    $emailVal = $email !== '' ? $email : null;
    $instVal = $instagram !== '' ? $instagram : null;
    $ytVal = $youtube !== '' ? $youtube : null;

    $stmtIns->bind_param(
        'sssssssss',
        $defaultImg,
        $nome,
        $funcao,
        $bioVal,
        $quoteVal,
        $linkedinVal,
        $emailVal,
        $instVal,
        $ytVal
    );
    $stmtIns->execute();
    $newId = $mysqli->insert_id;
    $stmtIns->close();

    // 2) Tratar upload de imagem (se existir)
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $extPerm = ['jpg', 'jpeg', 'png', 'gif'];
        $fileTmp = $_FILES['imagem']['tmp_name'];
        $fileName = $_FILES['imagem']['name'];
        $sizeOK = $_FILES['imagem']['size'] > 0 && $_FILES['imagem']['size'] < 5 * 1024 * 1024;
        $pathInfo = pathinfo($fileName);
        $ext = strtolower($pathInfo['extension']);

        if ($sizeOK && in_array($ext, $extPerm)) {
            $novoNome = "team_{$newId}.{$ext}";
            $destino = __DIR__ . "/../Imagens/Equipa/{$novoNome}";

            // Apaga qualquer ficheiro anterior com prefixo team_[ID].*
            foreach ($extPerm as $e) {
                $antigo = __DIR__ . "/../Imagens/Equipa/team_{$newId}.{$e}";
                if (file_exists($antigo)) {
                    unlink($antigo);
                }
            }

            move_uploaded_file($fileTmp, $destino);

            // Atualiza a coluna EQ_IMAGEM com o nome da nova imagem
            $stmtUpdImg = $mysqli->prepare("
              UPDATE KR_EQ_MEMBROS 
              SET EQ_IMAGEM = ? 
              WHERE EQ_ID = ?
            ");
            $stmtUpdImg->bind_param('si', $novoNome, $newId);
            $stmtUpdImg->execute();
            $stmtUpdImg->close();
        }
    }

    header('Location: equipa-adm.php?msg=added');
    exit;
}

// -----------------------------
// 5. EDITAR MEMBRO (POST action = edit)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'edit')) {
    $id = intval($_POST['eq_id']);
    $nome = trim($_POST['eq_nome'] ?? '');
    $funcao = trim($_POST['eq_funcao'] ?? '');
    $bio = trim($_POST['eq_bio'] ?? '');
    $quote = trim($_POST['eq_quote'] ?? '');
    $linkedin = trim($_POST['eq_linkedin'] ?? '');
    $email = trim($_POST['eq_email'] ?? '');
    $instagram = trim($_POST['eq_instagram'] ?? '');
    $youtube = trim($_POST['eq_youtube'] ?? '');

    if ($nome === '' || $funcao === '') {
        header('Location: equipa-adm.php?msg=error_missing');
        exit;
    }

    // 1) Obter imagem antiga para apagar se for substituída
    $stmtImg2 = $mysqli->prepare("SELECT EQ_IMAGEM FROM KR_EQ_MEMBROS WHERE EQ_ID = ?");
    $stmtImg2->bind_param('i', $id);
    $stmtImg2->execute();
    $res2 = $stmtImg2->get_result();
    $rowBefore = $res2->fetch_assoc();
    $oldFilename = $rowBefore['EQ_IMAGEM'] ?? null;
    $stmtImg2->close();

    // 2) Atualizar todos os campos (exceto imagem)
    $stmtUpd = $mysqli->prepare("
      UPDATE KR_EQ_MEMBROS
      SET
        EQ_NOME       = ?,
        EQ_FUNCAO     = ?,
        EQ_BIO        = ?,
        EQ_QUOTE      = ?,
        EQ_LINKEDIN   = ?,
        EQ_EMAIL      = ?,
        EQ_INSTAGRAM  = ?,
        EQ_YOUTUBE    = ?
      WHERE EQ_ID = ?
    ");
    $bioVal = $bio !== '' ? $bio : null;
    $quoteVal = $quote !== '' ? $quote : null;
    $linkedinVal = $linkedin !== '' ? $linkedin : null;
    $emailVal = $email !== '' ? $email : null;
    $instVal = $instagram !== '' ? $instagram : null;
    $ytVal = $youtube !== '' ? $youtube : null;

    $stmtUpd->bind_param(
        'ssssssssi',
        $nome,
        $funcao,
        $bioVal,
        $quoteVal,
        $linkedinVal,
        $emailVal,
        $instVal,
        $ytVal,
        $id
    );
    $stmtUpd->execute();
    $stmtUpd->close();

    // 3) Se houver upload de imagem, processar
    if (isset($_FILES['imagem_edit']) && $_FILES['imagem_edit']['error'] === UPLOAD_ERR_OK) {
        $extPerm = ['jpg', 'jpeg', 'png', 'gif'];
        $fileTmp = $_FILES['imagem_edit']['tmp_name'];
        $fileName = $_FILES['imagem_edit']['name'];
        $sizeOK = $_FILES['imagem_edit']['size'] > 0 && $_FILES['imagem_edit']['size'] < 5 * 1024 * 1024;
        $pathInfo = pathinfo($fileName);
        $ext = strtolower($pathInfo['extension']);

        if ($sizeOK && in_array($ext, $extPerm)) {
            $novoNome = "team_{$id}.{$ext}";
            $destino = __DIR__ . "/../Imagens/Equipa/{$novoNome}";

            // Apaga todos os ficheiros antigos com prefixo team_[ID].*
            foreach ($extPerm as $e) {
                $antigo = __DIR__ . "/../Imagens/Equipa/team_{$id}.{$e}";
                if (file_exists($antigo)) {
                    unlink($antigo);
                }
            }

            move_uploaded_file($fileTmp, $destino);

            $stmtUpdImg2 = $mysqli->prepare("
              UPDATE KR_EQ_MEMBROS 
              SET EQ_IMAGEM = ? 
              WHERE EQ_ID = ?
            ");
            $stmtUpdImg2->bind_param('si', $novoNome, $id);
            $stmtUpdImg2->execute();
            $stmtUpdImg2->close();

            apagarImagemAnterior($oldFilename);
        }
    }

    header('Location: equipa-adm.php?msg=edited');
    exit;
}

// -----------------------------
// 6. LER TODOS OS MEMBROS (para mostrar na tabela "Consultar")
// -----------------------------
$members = [];
$resultAll = $mysqli->query("
  SELECT
    EQ_ID, EQ_IMAGEM, EQ_NOME, EQ_FUNCAO, EQ_BIO, EQ_QUOTE,
    EQ_LINKEDIN, EQ_EMAIL, EQ_INSTAGRAM, EQ_YOUTUBE
  FROM KR_EQ_MEMBROS
  ORDER BY EQ_ID ASC
");
if ($resultAll) {
    while ($row = $resultAll->fetch_assoc()) {
        $members[] = $row;
    }
    $resultAll->free();
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

    <title>KR Legends - Gerir Equipa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../css/Geral.css" rel="stylesheet">
    <link href="../css/cookieConsent.css" rel="stylesheet">
    <link href="../css/includes/profileMenu.css" rel="stylesheet">
    <link href="../css/includes/modal-logout.css" rel="stylesheet">
    <link href="CSS/geral-adm.css" rel="stylesheet">
    <link href="CSS/equipa-adm.css" rel="stylesheet">
    <link href="CSS/equipa-adm-responsive.css" rel="stylesheet">
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
                    <button type="button" class="btn-close btn-close-white logout-btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
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
                        <!-- Modal Confirmar Logout -->
                        <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-yellow">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="logoutModalLabel">Confirmar Logout</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                            aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        Tem certeza de que deseja terminar a sessão?
                                    </div>
                                    <div class="modal-footer">
                                        <!-- Botão Cancelar -->
                                        <button type="button" class="btn btn-outline-light"
                                            data-bs-dismiss="modal">Cancelar</button>

                                        <!-- Formulário para confirmar o logout -->
                                        <form action="../PHP/logout.php" method="POST" style="display:inline-block;">
                                            <button type="submit" class="btn confirm-logout-btn">Terminar
                                                Sessão</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="profile-menu-container">
                            <!-- User profile name with link to profile page -->
                            <a href="perfil.php" class="profile-name" title="Ver perfil">
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
                                        <a class="dropdown-item d-flex align-items-center" href="#">
                                            <i class="bi bi-person-circle me-2"></i> Perfil
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="stats.php">
                                            <i class="bi bi-bar-chart-line me-2"></i> Estatísticas
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="settings.php">
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
                    <a href="equipa-adm.php" class="adm-sidebar-item disabled">
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
                    <a href="users-adm.php" class="adm-sidebar-item">
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

        <main class="eqadm-main">
            <!-- Cabeçalho da página -->
            <div class="eqadm-header">
                <h1 class="eqadm-title">Gestão da Equipa</h1>
                <p class="eqadm-subtitle">
                    Gerir membros da equipa de forma eficiente e organizada. Adicione, edite ou remova membros conforme
                    necessário.
                </p>
            </div>

            <!-- Barra de ações principal -->
            <div class="eqadm-actions-bar">
                <div class="eqadm-actions-left">
                    <!-- Campo de pesquisa -->
                    <div class="eqadm-search-container">
                        <i class="bi bi-search eqadm-search-icon"></i>
                        <input type="text" id="searchInput" class="eqadm-search-input"
                            placeholder="Pesquisar por ID ou Nome do membro..." />
                    </div>
                </div>
                <div class="eqadm-actions-right">
                    <!-- Botão de filtros -->
                    <button type="button" id="toggleFilters" class="eqadm-btn eqadm-btn-secondary"
                        title="Mostrar filtros">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <!-- Botão adicionar membro -->
                    <button type="button" class="eqadm-btn eqadm-btn-primary" onclick="openAddModal()">
                        <i class="bi bi-plus-lg"></i> Adicionar Membro
                    </button>
                </div>
            </div>

            <!-- Painel de filtros (inicialmente oculto) -->
            <div id="filterPanel" class="eqadm-filters-panel" style="display: none;">
                <div class="eqadm-filters-grid">
                    <!-- Filtrar por Função -->
                    <div class="eqadm-filter-group">
                        <label for="filterRole" class="eqadm-filter-label">Filtrar por Função:</label>
                        <select id="filterRole" class="eqadm-filter-select">
                            <option value="">Todas as funções</option>
                            <?php
                            // Preenche opções dinamicamente com as funções existentes na BD
                            $rolesStmt = $mysqli->query("SELECT DISTINCT EQ_FUNCAO FROM KR_EQ_MEMBROS ORDER BY EQ_FUNCAO ASC");
                            while ($roleRow = $rolesStmt->fetch_assoc()) {
                                $func = htmlspecialchars($roleRow['EQ_FUNCAO']);
                                echo "<option value=\"{$func}\">{$func}</option>";
                            }
                            $rolesStmt->free();
                            ?>
                        </select>
                    </div>

                    <!-- Ordenar por ID -->
                    <div class="eqadm-filter-group">
                        <label for="filterOrder" class="eqadm-filter-label">Ordenar por ID:</label>
                        <select id="filterOrder" class="eqadm-filter-select">
                            <option value="asc">Crescente (1, 2, 3...)</option>
                            <option value="desc">Decrescente (...3, 2, 1)</option>
                        </select>
                    </div>

                    <!-- Filtrar por extensão de imagem -->
                    <div class="eqadm-filter-group">
                        <label for="filterExt" class="eqadm-filter-label">Tipo de Imagem:</label>
                        <select id="filterExt" class="eqadm-filter-select">
                            <option value="">Todos os tipos</option>
                            <option value="jpg">JPG</option>
                            <option value="jpeg">JPEG</option>
                            <option value="png">PNG</option>
                            <option value="gif">GIF</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Mensagens de feedback -->
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'added'): ?>
                    <div class="eqadm-feedback eqadm-feedback-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Novo membro adicionado com sucesso!
                    </div>
                <?php elseif ($_GET['msg'] === 'edited'): ?>
                    <div class="eqadm-feedback eqadm-feedback-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Membro editado com sucesso!
                    </div>
                <?php elseif ($_GET['msg'] === 'deleted'): ?>
                    <div class="eqadm-feedback eqadm-feedback-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Membro eliminado com sucesso.
                    </div>
                <?php elseif ($_GET['msg'] === 'error_missing'): ?>
                    <div class="eqadm-feedback eqadm-feedback-error">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Erro: É obrigatório inserir Nome e Função do membro.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Tabela de membros -->
            <div class="eqadm-table-container">
                <table class="eqadm-table" id="membersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Nome</th>
                            <th>Função</th>
                            <th>Bio</th>
                            <th>Quote</th>
                            <th>LinkedIn</th>
                            <th>Email</th>
                            <th>Instagram</th>
                            <th>YouTube</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="11" class="eqadm-text-center"
                                    style="padding: 2rem; color: var(--color-gray-400);">
                                    <i class="bi bi-people"
                                        style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                    Nenhum membro da equipa encontrado.<br>
                                    <small>Comece por adicionar o primeiro membro da equipa.</small>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($members as $m): ?>
                            <tr>
                                <td class="cell-id"><?= htmlspecialchars($m['EQ_ID']) ?></td>
                                <td class="eqadm-text-center">
                                    <img src="../Imagens/Equipa/<?= htmlspecialchars($m['EQ_IMAGEM']) ?>"
                                        alt="Foto de <?= htmlspecialchars($m['EQ_NOME']) ?>" class="eqadm-avatar"
                                        title="<?= htmlspecialchars($m['EQ_NOME']) ?>" />
                                </td>
                                <td class="cell-nome"><?= htmlspecialchars($m['EQ_NOME']) ?></td>
                                <td class="cell-funcao"><?= htmlspecialchars($m['EQ_FUNCAO']) ?></td>
                                <td class="eqadm-text-truncate" title="<?= htmlspecialchars($m['EQ_BIO'] ?? '') ?>">
                                    <?= htmlspecialchars($m['EQ_BIO'] ?? '') ?>
                                </td>
                                <td class="eqadm-text-truncate" title="<?= htmlspecialchars($m['EQ_QUOTE'] ?? '') ?>">
                                    <?= htmlspecialchars($m['EQ_QUOTE'] ?? '') ?>
                                </td>
                                <td class="eqadm-text-center">
                                    <?php if (!empty($m['EQ_LINKEDIN'])): ?>
                                        <a href="<?= htmlspecialchars($m['EQ_LINKEDIN']) ?>" target="_blank"
                                            class="eqadm-icon-link" title="Ver LinkedIn">
                                            <i class="bi bi-linkedin"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="eqadm-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="eqadm-text-center">
                                    <?php if (!empty($m['EQ_EMAIL'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($m['EQ_EMAIL']) ?>" class="eqadm-icon-link"
                                            title="Enviar email">
                                            <i class="bi bi-envelope-fill"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="eqadm-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="eqadm-text-center">
                                    <?php if (!empty($m['EQ_INSTAGRAM'])): ?>
                                        <a href="<?= htmlspecialchars($m['EQ_INSTAGRAM']) ?>" target="_blank"
                                            class="eqadm-icon-link" title="Ver Instagram">
                                            <i class="bi bi-instagram"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="eqadm-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="eqadm-text-center">
                                    <?php if (!empty($m['EQ_YOUTUBE'])): ?>
                                        <a href="<?= htmlspecialchars($m['EQ_YOUTUBE']) ?>" target="_blank"
                                            class="eqadm-icon-link" title="Ver YouTube">
                                            <i class="bi bi-youtube"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="eqadm-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="eqadm-table-actions">
                                        <button class="eqadm-btn eqadm-btn-secondary eqadm-btn-sm" title="Editar membro"
                                            onclick="openEditModal(
                                                    <?= $m['EQ_ID'] ?>,
                                                    '<?= addslashes($m['EQ_NOME']) ?>',
                                                    '<?= addslashes($m['EQ_FUNCAO']) ?>',
                                                    '<?= addslashes($m['EQ_BIO'] ?? '') ?>',
                                                    '<?= addslashes($m['EQ_QUOTE'] ?? '') ?>',
                                                    '<?= addslashes($m['EQ_LINKEDIN'] ?? '') ?>',
                                                    '<?= addslashes($m['EQ_EMAIL'] ?? '') ?>',
                                                    '<?= addslashes($m['EQ_INSTAGRAM'] ?? '') ?>',
                                                    '<?= addslashes($m['EQ_YOUTUBE'] ?? '') ?>'
                                                )">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <a href="equipa-adm.php?delete_id=<?= $m['EQ_ID'] ?>"
                                            class="eqadm-btn eqadm-btn-danger eqadm-btn-sm" title="Eliminar membro">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- A paginação será inserida aqui dinamicamente pelo JavaScript -->
        </main>

        <!-- MODAL "Adicionar Membro" -->
        <div id="modalAdd" class="eqadm-modal-backdrop">
            <div class="eqadm-modal-content">
                <h2><i class="bi bi-person-plus-fill me-2"></i>Adicionar Novo Membro</h2>
                <form action="equipa-adm.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add" />

                    <div class="eqadm-form-grid">
                        <div class="eqadm-form-group">
                            <label for="eq_nome">Nome Completo *</label>
                            <input type="text" name="eq_nome" id="eq_nome" required placeholder="Ex: João Silva" />
                        </div>

                        <div class="eqadm-form-group">
                            <label for="eq_funcao">Função na Equipa *</label>
                            <input type="text" name="eq_funcao" id="eq_funcao" required
                                placeholder="Ex: Desenvolvedor Frontend" />
                        </div>

                        <div class="eqadm-form-group full-width">
                            <label for="eq_bio">Biografia</label>
                            <textarea name="eq_bio" id="eq_bio" rows="3"
                                placeholder="Breve descrição sobre o membro da equipa..."></textarea>
                        </div>

                        <div class="eqadm-form-group full-width">
                            <label for="eq_quote">Quote Inspiracional</label>
                            <input type="text" name="eq_quote" id="eq_quote"
                                placeholder="Uma frase motivacional ou citação..." />
                        </div>

                        <div class="eqadm-form-group">
                            <label for="eq_linkedin">LinkedIn</label>
                            <input type="url" name="eq_linkedin" id="eq_linkedin"
                                placeholder="https://linkedin.com/in/..." />
                        </div>

                        <div class="eqadm-form-group">
                            <label for="eq_email">Email</label>
                            <input type="email" name="eq_email" id="eq_email" placeholder="exemplo@krlegends.com" />
                        </div>

                        <div class="eqadm-form-group">
                            <label for="eq_instagram">Instagram</label>
                            <input type="url" name="eq_instagram" id="eq_instagram"
                                placeholder="https://instagram.com/..." />
                        </div>

                        <div class="eqadm-form-group">
                            <label for="eq_youtube">YouTube</label>
                            <input type="url" name="eq_youtube" id="eq_youtube" placeholder="https://youtube.com/..." />
                        </div>

                        <div class="eqadm-form-group full-width">
                            <label for="imagem">Fotografia do Membro</label>
                            <input type="file" name="imagem" id="imagem" accept=".jpg,.jpeg,.png,.gif" />
                            <small>Formatos aceites: JPG, JPEG, PNG, GIF. Tamanho máximo: 5MB. Se não escolher, será
                                usada a imagem padrão.</small>
                        </div>
                    </div>

                    <div class="eqadm-modal-actions">
                        <button type="button" class="eqadm-btn eqadm-btn-secondary" onclick="closeAddModal()">
                            <i class="bi bi-x-lg me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="eqadm-btn eqadm-btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Guardar Membro
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL "Editar Membro" -->
        <div id="modalEdit" class="eqadm-modal-backdrop">
            <div class="eqadm-modal-content">
                <h2><i class="bi bi-pencil-square me-2"></i>Editar Membro</h2>
                <form action="equipa-adm.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit" />
                    <input type="hidden" name="eq_id" id="edit_eq_id" value="" />

                    <div class="eqadm-form-grid">
                        <div class="eqadm-form-group">
                            <label for="edit_eq_nome">Nome Completo *</label>
                            <input type="text" name="eq_nome" id="edit_eq_nome" required />
                        </div>

                        <div class="eqadm-form-group">
                            <label for="edit_eq_funcao">Função na Equipa *</label>
                            <input type="text" name="eq_funcao" id="edit_eq_funcao" required />
                        </div>

                        <div class="eqadm-form-group full-width">
                            <label for="edit_eq_bio">Biografia</label>
                            <textarea name="eq_bio" id="edit_eq_bio" rows="3"></textarea>
                        </div>

                        <div class="eqadm-form-group full-width">
                            <label for="edit_eq_quote">Quote Inspiracional</label>
                            <input type="text" name="eq_quote" id="edit_eq_quote" />
                        </div>

                        <div class="eqadm-form-group">
                            <label for="edit_eq_linkedin">LinkedIn</label>
                            <input type="url" name="eq_linkedin" id="edit_eq_linkedin" />
                        </div>

                        <div class="eqadm-form-group">
                            <label for="edit_eq_email">Email</label>
                            <input type="email" name="eq_email" id="edit_eq_email" />
                        </div>

                        <div class="eqadm-form-group">
                            <label for="edit_eq_instagram">Instagram</label>
                            <input type="url" name="eq_instagram" id="edit_eq_instagram" />
                        </div>

                        <div class="eqadm-form-group">
                            <label for="edit_eq_youtube">YouTube</label>
                            <input type="url" name="eq_youtube" id="edit_eq_youtube" />
                        </div>

                        <div class="eqadm-form-group full-width">
                            <label for="imagem_edit">Nova Fotografia (opcional)</label>
                            <input type="file" name="imagem_edit" id="imagem_edit" accept=".jpg,.jpeg,.png,.gif" />
                            <small>Se não escolher nada, a imagem atual será mantida.</small>
                        </div>
                    </div>

                    <div class="eqadm-modal-actions">
                        <button type="button" class="eqadm-btn eqadm-btn-secondary" onclick="closeEditModal()">
                            <i class="bi bi-x-lg me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="eqadm-btn eqadm-btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Guardar Alterações
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
    <script src="JS/equipa-adm.js"></script>
    <script src="../js/includes/profileMenu.js"></script>
    <script src="../js/Gerais/Geral.js"></script>
    <script src="../js/Gerais/cookieConsent.js"></script>
    <script src="../js/Gerais/notification_API.js" defer></script>
</body>
</html>