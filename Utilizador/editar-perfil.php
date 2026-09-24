<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();
$userId = $_SESSION['id'] ?? null;

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: /~a29621/KRLegends/Index.php");
    exit();
}
require_once __DIR__ . '/../../databaseconnect.php';
require_once __DIR__ . '/social-functions.php';
require_once __DIR__ . '/roblox-functions.php';

$exts = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
$mensagem = '';
$avatarName = $_SESSION['user_avatar'] ?? 'avatar_default';
$bannerName = $_SESSION['banner'] ?? 'banner_default';
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$maxSize = 6 * 1024 * 1024;

// 0) Buscar o ROBLOX_USERNAME atual diretamente na BD
$robloxUsernameAtual = null;
$stmtFetch = $mysqli->prepare("
    SELECT ROBLOX_USERNAME
      FROM KR_USERS
     WHERE ID = ?
");
$stmtFetch->bind_param("i", $userId);
$stmtFetch->execute();
$resultFetch = $stmtFetch->get_result();
if ($rowFetch = $resultFetch->fetch_assoc()) {
    $robloxUsernameAtual = $rowFetch['ROBLOX_USERNAME']; // pode ser string ou NULL
}
$stmtFetch->close();

// Variável usada no HTML e para comparação
$roblox_username = $robloxUsernameAtual;

// Formato dd/mm/aaaa para mostrar no input
$dateOfBirthValue = '';
if (!empty($_SESSION['date_of_birth'])) {
    $d = DateTime::createFromFormat('Y-m-d', $_SESSION['date_of_birth']);
    if ($d) {
        $dateOfBirthValue = $d->format('d/m/Y');
    }
}

// Inicializa variáveis para redes sociais
$socials = $_SESSION['socials'] ?? [
    'SOCIAL_ID' => $userId,
    'EMAIL_SOCIAL' => '',
    'WHATSAPP' => '',
    'YOUTUBE' => '',
    'INSTAGRAM' => '',
    'TIKTOK' => '',
    'FACEBOOK' => '',
    'LINKEDIN' => '',
    'GITHUB' => '',
    'TWITTER' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Captura os campos
    $nome = trim($_POST['name'] ?? '');
    $user_name = trim($_POST['user_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $pais = trim($_POST['country'] ?? '');
    $cidade = trim($_POST['city'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $dobRaw = trim($_POST['date_of_birth'] ?? '');

    // Captura dados de redes sociais
    $socialFields = [
        'EMAIL_SOCIAL',
        'WHATSAPP',
        'YOUTUBE',
        'INSTAGRAM',
        'TIKTOK',
        'FACEBOOK',
        'LINKEDIN',
        'GITHUB',
        'TWITTER'
    ];
    $newSocials = ['SOCIAL_ID' => $userId];
    $hasValidSocial = false;
    foreach ($socialFields as $field) {
        $value = trim($_POST[$field] ?? '');
        $newSocials[$field] = $value;
        if (!empty($value) && isValidUrl($value)) {
            $hasValidSocial = true;
        }
    }

    // Converte dd/mm/aaaa → YYYY-MM-DD ou null
    if ($dobRaw === '') {
        $dateOfBirth = null;
    } else {
        $d = DateTime::createFromFormat('d/m/Y', $dobRaw);
        if ($d && $d->format('d/m/Y') === $dobRaw) {
            $dateOfBirth = $d->format('Y-m-d');
        } else {
            $mensagem = 'Data de nascimento inválida. Utilize o formato dd/mm/aaaa.';
        }
    }

    // 2) Validações iniciais
    if (empty($mensagem)) {
        if (strlen($bio) > 500) {
            $mensagem = 'A biografia é demasiado longa (máximo 500 caracteres).';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $user_name)) {
            $mensagem = 'O nome de utilizador só pode conter letras, números e underscores.';
        } else {
            // 3) Verifica duplicação de username
            $check = $mysqli->prepare("
                SELECT 1 
                  FROM KR_USERS 
                 WHERE KR_USER_NAME = ? 
                   AND KR_EMAIL_USER != ?
            ");
            $check->bind_param("ss", $user_name, $_SESSION['user_email']);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $mensagem = 'Esse nome de utilizador já está em uso por outro utilizador.';
            }
            $check->close();
        }
    }

    // 4) Tratamento de uploads (avatar e banner) antes do UPDATE
    if (empty($mensagem)) {
        // Avatar
        if (!empty($_FILES['avatar']['tmp_name']) && $_FILES['avatar']['error'] === 0) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $newAvatar = "avatar_{$userId}.{$ext}";
            $uploadPath = __DIR__ . '/../Imagens/avatares/' . $newAvatar;
            // Apagar versões antigas
            foreach ($exts as $e) {
                $oldFile = __DIR__ . "/../Imagens/avatares/avatar_{$userId}.{$e}";
                if ($e !== $ext && file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                $avatarName = $newAvatar;
            } else {
                $mensagem = "Erro ao mover o novo avatar.";
            }
        }
        // Banner
        if (empty($mensagem) && !empty($_FILES['banner']['tmp_name']) && $_FILES['banner']['error'] === 0) {
            $tmp = $_FILES['banner']['tmp_name'];
            $type = mime_content_type($tmp);
            $size = $_FILES['banner']['size'];
            if (in_array($type, $allowedTypes) && $size <= $maxSize) {
                $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
                $newBanner = "banner_{$userId}.{$ext}";
                $destBanner = __DIR__ . '/../Imagens/banners/' . $newBanner;
                foreach ($exts as $e) {
                    $oldFile = __DIR__ . "/../Imagens/banners/banner_{$userId}.{$e}";
                    if ($e !== $ext && file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                if (move_uploaded_file($tmp, $destBanner)) {
                    $bannerName = $newBanner;
                } else {
                    $mensagem = 'Erro ao mover o novo banner.';
                }
            } else {
                $mensagem = 'Banner inválido (tipo ou tamanho incorreto).';
            }
        }
    }

    // 5) Validação condicional de ROBLOX_USERNAME
    $novoRobloxUsername = $robloxUsernameAtual; // valor por defeito
    // Em editar-perfil.php, na seção de validação do Roblox
    if (empty($mensagem) && isset($_POST['ROBLOX_USERNAME'])) {
        $robloxRaw = trim($_POST['ROBLOX_USERNAME']);
        if ($robloxRaw === '') {
            $novoRobloxUsername = null;
            // Limpa o cache se existir
            $cacheFile = ROBLOX_CACHE_DIR . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $robloxUsernameAtual) . '.json';
            if (file_exists($cacheFile)) {
                @unlink($cacheFile);
            }
        } else {
            if ($robloxRaw !== $robloxUsernameAtual) {
                $validation = validateRobloxUsername($robloxRaw);
                if (!$validation['valid']) {
                    $mensagem = $validation['error'];
                } else {
                    $novoRobloxUsername = $validation['data']['name'];
                    // Atualiza o cache imediatamente
                    $robloxData = getRobloxUserData($novoRobloxUsername);
                    if ($robloxData) {
                        writeRobloxCache($novoRobloxUsername, $robloxData);
                    }
                }
            }
        }
    }

    // 6) Atualiza a BD se não houver mensagem de erro
    if (empty($mensagem)) {
        // Montar UPDATE dinâmico
        $setClauses = [];
        $bindTypes = "";
        $bindValues = [];

        // Campos fixos (sempre atualizamos)
        $setClauses[] = "KR_NAME = ?";
        $bindTypes .= "s";
        $bindValues[] = $nome;

        $setClauses[] = "KR_USER_NAME = ?";
        $bindTypes .= "s";
        $bindValues[] = $user_name;

        $setClauses[] = "KR_BIO = ?";
        $bindTypes .= "s";
        $bindValues[] = $bio;

        $setClauses[] = "KR_COUNTRY = ?";
        $bindTypes .= "s";
        $bindValues[] = $pais;

        $setClauses[] = "KR_CITY = ?";
        $bindTypes .= "s";
        $bindValues[] = $cidade;

        $setClauses[] = "KR_GENERO = ?";
        $bindTypes .= "s";
        $bindValues[] = $genero;

        $setClauses[] = "KR_DATE_OF_BIRTH = ?";
        $bindTypes .= "s";
        $bindValues[] = $dateOfBirth;

        $setClauses[] = "KR_AVATAR = ?";
        $bindTypes .= "s";
        $bindValues[] = $avatarName;

        $setClauses[] = "KR_BANNER = ?";
        $bindTypes .= "s";
        $bindValues[] = $bannerName;

        $setClauses[] = "ROBLOX_USERNAME = ?";
        $bindTypes .= "s";
        $bindValues[] = $novoRobloxUsername;


        // Se o nome do Roblox mudou, atualizar cache JSON agora
        if ($novoRobloxUsername !== $robloxUsernameAtual && $novoRobloxUsername !== null) {
            $robloxData = getRobloxUserData($novoRobloxUsername);
            if ($robloxData) {
                writeRobloxCache($novoRobloxUsername, $robloxData);
            }
        }

        // Condição WHERE
        $whereClause = "WHERE KR_EMAIL_USER = ?";
        $bindTypes .= "s";
        $bindValues[] = $_SESSION['user_email'];

        // Monta SQL final
        $sql = "
            UPDATE KR_USERS
               SET " . implode(",\n                   ", $setClauses) . "
             $whereClause
        ";
        $upd = $mysqli->prepare($sql);
        if (!$upd) {
            $mensagem = "Erro ao preparar UPDATE: " . $mysqli->error;
        } else {
            // Função bind_param requer referências
            $params = array_merge([$bindTypes], $bindValues);
            $refArr = [];
            foreach ($params as $key => $value) {
                $refArr[$key] = &$params[$key];
            }
            call_user_func_array([$upd, 'bind_param'], $refArr);

            if (!$upd->execute()) {
                $mensagem = 'Erro ao atualizar perfil: ' . $mysqli->error;
            } else {
                // 7) Atualiza redes sociais
                $checkSocial = $mysqli->prepare("SELECT 1 FROM KR_USER_SOCIALS WHERE SOCIAL_ID = ?");
                $checkSocial->bind_param("i", $userId);
                $checkSocial->execute();
                $checkSocial->store_result();

                if ($checkSocial->num_rows > 0) {
                    // UPDATE existing
                    $updSocial = $mysqli->prepare("
                        UPDATE KR_USER_SOCIALS SET
                            EMAIL_SOCIAL = ?,
                            WHATSAPP     = ?,
                            YOUTUBE      = ?,
                            INSTAGRAM    = ?,
                            TIKTOK       = ?,
                            FACEBOOK     = ?,
                            LINKEDIN     = ?,
                            GITHUB       = ?,
                            TWITTER      = ?
                        WHERE SOCIAL_ID = ?
                    ");
                    $updSocial->bind_param(
                        "sssssssssi",
                        $newSocials['EMAIL_SOCIAL'],
                        $newSocials['WHATSAPP'],
                        $newSocials['YOUTUBE'],
                        $newSocials['INSTAGRAM'],
                        $newSocials['TIKTOK'],
                        $newSocials['FACEBOOK'],
                        $newSocials['LINKEDIN'],
                        $newSocials['GITHUB'],
                        $newSocials['TWITTER'],
                        $userId
                    );
                    if (!$updSocial->execute()) {
                        $mensagem = 'Erro ao atualizar redes sociais: ' . $mysqli->error;
                    }
                    $updSocial->close();
                } else {
                    // INSERT new
                    $insSocial = $mysqli->prepare("
                        INSERT INTO KR_USER_SOCIALS (
                            SOCIAL_ID,
                            EMAIL_SOCIAL,
                            WHATSAPP,
                            YOUTUBE,
                            INSTAGRAM,
                            TIKTOK,
                            FACEBOOK,
                            LINKEDIN,
                            GITHUB,
                            TWITTER
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $insSocial->bind_param(
                        "isssssssss",
                        $userId,
                        $newSocials['EMAIL_SOCIAL'],
                        $newSocials['WHATSAPP'],
                        $newSocials['YOUTUBE'],
                        $newSocials['INSTAGRAM'],
                        $newSocials['TIKTOK'],
                        $newSocials['FACEBOOK'],
                        $newSocials['LINKEDIN'],
                        $newSocials['GITHUB'],
                        $newSocials['TWITTER']
                    );
                    if (!$insSocial->execute()) {
                        $mensagem = 'Erro ao inserir redes sociais: ' . $mysqli->error;
                    }
                    $insSocial->close();
                }
                $checkSocial->close();

                if (empty($mensagem)) {
                    // 8) Atualiza sessão
                    $_SESSION['name'] = $nome;
                    $_SESSION['user_name'] = $user_name;
                    $_SESSION['bio'] = $bio;
                    $_SESSION['country'] = $pais;
                    $_SESSION['city'] = $cidade;
                    $_SESSION['genero'] = $genero;
                    $_SESSION['user_avatar'] = $avatarName;
                    $_SESSION['banner'] = $bannerName;
                    $_SESSION['date_of_birth'] = $dateOfBirth;
                    $_SESSION['socials'] = $newSocials;
                    $_SESSION['roblox_username'] = $novoRobloxUsername;

                    // Se o nome do Roblox mudou, atualizar cache JSON agora
                    if ($novoRobloxUsername !== $robloxUsernameAtual && $novoRobloxUsername !== null) {
                        $robloxData = getRobloxUserData($novoRobloxUsername);
                        // Grava manualmente no cache (sobrescreve)
                        writeRobloxCache($novoRobloxUsername, $robloxData ?: []);
                    }

                    $mensagem = 'Perfil atualizado com sucesso.';
                }
            }
            $upd->close();
        }
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

    <title>KR Legends - Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../css/Geral.css" rel="stylesheet">
    <link href="../css/cookieConsent.css" rel="stylesheet">
    <link href="../css/includes/modal-logout.css" rel="stylesheet">
    <link href="../css/includes/profileMenu.css" rel="stylesheet">
    <link href="CSS/editar-perfil.css" rel="stylesheet">
    <link href="CSS/editar-perfil-responsive.css" rel="stylesheet">
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
                                        <a class="dropdown-item d-flex align-items-center" href="perfil.php">
                                            <i class="bi bi-person-circle me-2"></i> Perfil
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="stats.php">
                                            <i class="bi bi-bar-chart-line me-2"></i> Estatísticas
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center"
                                            href="Utilozador/settings.php">
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
                                $adminUrl = '../admin/modoADM.php';
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

    <div class="container mt-5">
        <h2 class="mb-4">Editar Perfil</h2>

        <?php if ($mensagem): ?>
            <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form id="form_personalised" method="post" enctype="multipart/form-data">
            <!-- Seção de Imagens do Perfil -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-image me-2"></i>Imagens do Perfil
                </div>

                <!-- Avatar -->
                <div class="mb-3 media-preview-container">
                    <label for="avatar" class="form-label">Avatar</label>
                    <img src="../Imagens/avatares/<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'avatar_default') ?>"
                        alt="Avatar atual" class="perfil-avatar mb-2">
                    <div class="image-upload-container">
                        <label for="avatar" class="custom-file-upload">
                            <i class="fas fa-upload"></i> Selecionar nova imagem de avatar
                        </label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*"
                            style="display: none;">
                    </div>
                </div>

                <!-- Banner -->
                <div class="mb-3 media-preview-container">
                    <label for="banner" class="form-label">Banner</label>
                    <img src="../Imagens/banners/<?= htmlspecialchars(($_SESSION['banner'] ?? 'banner_default')) ?>"
                        alt="Banner atual" class="perfil-banner mb-2">
                    <div class="image-upload-container">
                        <label for="banner" class="custom-file-upload">
                            <i class="fas fa-upload"></i> Selecionar nova imagem de banner
                        </label>
                        <input type="file" class="form-control" id="banner" name="banner" accept="image/*"
                            style="display: none;">
                    </div>
                </div>
            </div>

            <!-- Seção de Informações Pessoais -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-user me-2"></i>Informações Pessoais
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="user_name" class="form-label">Username</label>
                        <input type="text" class="form-control" id="user_name" name="user_name"
                            value="<?= htmlspecialchars($_SESSION['user_name']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="bio" class="form-label">
                        Biografia <span id="char-count">0</span>/500
                    </label>
                    <textarea class="form-control" id="bio" name="bio" maxlength="500" rows="3"
                        oninput="updateCharCount()"><?= htmlspecialchars($_SESSION['bio'] ?? '') ?></textarea>
                    <div class="form-text">Compartilhe um pouco sobre você em até 500 caracteres.</div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date_of_birth" class="form-label">Data de Nascimento</label>
                        <input type="text" class="form-control" id="date_of_birth" name="date_of_birth"
                            placeholder="dd/mm/aaaa" value="<?= htmlspecialchars($dateOfBirthValue) ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="country" class="form-label">País</label>
                        <input type="text" class="form-control" id="country" name="country"
                            value="<?= htmlspecialchars($_SESSION['country'] ?? '') ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="city" class="form-label">Cidade</label>
                        <input type="text" class="form-control" id="city" name="city"
                            value="<?= htmlspecialchars($_SESSION['city'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="genero" class="form-label">Gênero</label>
                    <select class="form-select" id="genero" name="genero">
                        <option value="Masculino" <?= ($_SESSION['genero'] ?? '') == 'Masculino' ? 'selected' : '' ?>>
                            Masculino
                        </option>
                        <option value="Feminino" <?= ($_SESSION['genero'] ?? '') == 'Feminino' ? 'selected' : '' ?>>
                            Feminino
                        </option>
                        <option value="Prefiro não divulgar" <?= ($_SESSION['genero'] ?? '') == 'Prefiro não divulgar' ? 'selected' : '' ?>>Prefiro não divulgar</option>
                    </select>
                </div>
            </div>

            <!-- Seção de Roblox -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-gamepad me-2"></i>Perfil do Roblox
                </div>

                <div class="mb-3">
                    <label for="roblox_username" class="form-label">Nome de utilizador do Roblox</label>
                    <input type="text" class="form-control" id="roblox_username" name="ROBLOX_USERNAME"
                        value="<?php echo $roblox_username; ?>" data-original-value="<?php echo $roblox_username; ?>">
                    <div class="form-text">Para que o seu username do Roblox seja associado corretamente, aguarde cerca
                        de 70 segundos entre cada alteração de valor.</div>
                </div>
            </div>

            <!-- Seção de Redes Sociais -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-share-alt me-2"></i>Redes Sociais
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="EMAIL_SOCIAL" class="form-label">
                            <i class="fas fa-envelope me-2"></i> Email de Contato
                        </label>
                        <input type="email" class="form-control" id="EMAIL_SOCIAL" name="EMAIL_SOCIAL"
                            placeholder="email@exemplo.com"
                            value="<?= htmlspecialchars($socials['EMAIL_SOCIAL'] ?? '') ?>">
                        <div class="form-text">Email para contato profissional</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="WHATSAPP" class="form-label">
                            <i class="fab fa-whatsapp me-2"></i> WhatsApp
                        </label>
                        <input type="text" class="form-control" id="WHATSAPP" name="WHATSAPP"
                            placeholder="https://wa.me/seu-numero"
                            value="<?= htmlspecialchars($socials['WHATSAPP'] ?? '') ?>">
                        <div class="form-text">Link do WhatsApp (ex: https://wa.me/351912345678)</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="INSTAGRAM" class="form-label">
                            <i class="fab fa-instagram me-2"></i> Instagram
                        </label>
                        <input type="text" class="form-control" id="INSTAGRAM" name="INSTAGRAM"
                            placeholder="https://instagram.com/seu-usuario"
                            value="<?= htmlspecialchars($socials['INSTAGRAM'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="FACEBOOK" class="form-label">
                            <i class="fab fa-facebook me-2"></i> Facebook
                        </label>
                        <input type="text" class="form-control" id="FACEBOOK" name="FACEBOOK"
                            placeholder="https://facebook.com/seu-perfil"
                            value="<?= htmlspecialchars($socials['FACEBOOK'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="TIKTOK" class="form-label">
                            <i class="fab fa-tiktok me-2"></i> TikTok
                        </label>
                        <input type="text" class="form-control" id="TIKTOK" name="TIKTOK"
                            placeholder="https://tiktok.com/@seu-usuario"
                            value="<?= htmlspecialchars($socials['TIKTOK'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="YOUTUBE" class="form-label">
                            <i class="fab fa-youtube me-2"></i> YouTube
                        </label>
                        <input type="text" class="form-control" id="YOUTUBE" name="YOUTUBE"
                            placeholder="https://youtube.com/c/seu-canal"
                            value="<?= htmlspecialchars($socials['YOUTUBE'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="TWITTER" class="form-label">
                            <i class="fab fa-twitter me-2"></i> Twitter
                        </label>
                        <input type="text" class="form-control" id="TWITTER" name="TWITTER"
                            placeholder="https://twitter.com/seu-usuario"
                            value="<?= htmlspecialchars($socials['TWITTER'] ?? '') ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="LINKEDIN" class="form-label">
                            <i class="fab fa-linkedin me-2"></i> LinkedIn
                        </label>
                        <input type="text" class="form-control" id="LINKEDIN" name="LINKEDIN"
                            placeholder="https://linkedin.com/in/seu-perfil"
                            value="<?= htmlspecialchars($socials['LINKEDIN'] ?? '') ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="GITHUB" class="form-label">
                            <i class="fab fa-github me-2"></i> GitHub
                        </label>
                        <input type="text" class="form-control" id="GITHUB" name="GITHUB"
                            placeholder="https://github.com/seu-usuario"
                            value="<?= htmlspecialchars($socials['GITHUB'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="buttons-container">
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-save me-2"></i> Guardar Alterações
                </button>
                <a href="perfil.php" class="btn btn-cancel">
                    <i class="fas fa-times me-2"></i> Cancelar
                </a>
            </div>
        </form>
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
    <script src="../js/Info_produtos.js"></script>
    <script src="../js/Gerais/Geral.js"></script>
    <script src="JS/editar-perfil.js"></script>
    <script src="JS/editar-perfil-customizations.js"></script>
    <script src="../js/Gerais/cookieConsent.js"></script>
    <script src="../js/Gerais/notification_API.js" defer></script>
</body>

</html>