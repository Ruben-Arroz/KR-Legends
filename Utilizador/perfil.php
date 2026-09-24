<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();
// Verifica se o utilizador está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: /~a29621/KRLegends/Index.php");
    exit();
}
require_once __DIR__ . '/social-functions.php';
require_once __DIR__ . '/roblox-functions.php';

$exts = ['png', 'jpg', 'jpeg', 'webp', 'gif'];

// --- AVATAR ---
$avatarBase = $_SESSION['user_avatar'] ?? 'avatar_default';
$avatarFinal = 'avatar_default.png';
foreach ($exts as $e) {
    $p = __DIR__ . '/../Imagens/avatares/' . $avatarBase . '.' . $e;
    if (file_exists($p)) {
        $avatarFinal = $avatarBase . '.' . $e;
        break;
    }
}

// --- BANNER ---
$bannerBase = $_SESSION['banner'] ?? 'banner_default';
$bannerFinal = 'banner_default.png';
foreach ($exts as $e) {
    $p = __DIR__ . '/../Imagens/banners/' . $bannerBase . '.' . $e;
    if (file_exists($p)) {
        $bannerFinal = $bannerBase . '.' . $e;
        break;
    }
}

// Verificar se existem redes sociais válidas
$hasSocialMedia = isset($_SESSION['socials']) && hasAnySocialMedia($_SESSION['socials']);
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

    <title>KR Legends - Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../css/Geral.css" rel="stylesheet">
    <link href="../css/cookieConsent.css" rel="stylesheet">
    <link href="../css/includes/profileMenu.css" rel="stylesheet">
    <link href="../css/includes/modal-logout.css" rel="stylesheet">
    <link href="CSS/perfil.css" rel="stylesheet">
    <link href="CSS/perfil-animations.css" rel="stylesheet">
    <link href="CSS/perfil-components.css" rel="stylesheet">
    <link href="CSS/social-card.css" rel="stylesheet">
    <link href="CSS/roblox-card.css" rel="stylesheet">
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

    <!-- Conteúdo Principal -->
    <main class="container py-5 mt-5">
        <!-- Seção do Perfil -->
        <section class="profile-header animate-on-load">
            <!-- Banner do Perfil -->
            <div class="profile-banner-container">
                <div class="profile-banner"
                    style="background-image: url('../Imagens/banners/<?= htmlspecialchars($_SESSION['banner']) ?>')">
                </div>
            </div>

            <!-- Avatar e Identificação -->
            <div class="profile-avatar-container">
                <img src="../Imagens/avatares/<?= htmlspecialchars($_SESSION['user_avatar']) ?>"
                    alt="Avatar do utilizador" class="profile-avatar pulse-on-hover" />
            </div>

            <div class="profile-identity">
                <h1 id="nometitle"><?= htmlspecialchars($_SESSION['name'] ?: $_SESSION['user_name']) ?></h1>
                <p class="profile-username">@<?= htmlspecialchars($_SESSION['user_name']) ?></p>

                <?php
                // Mapeamento de papéis para rótulos e estilos
                $roleLabels = [
                    'USER' => ['label' => 'Utilizador', 'class' => 'bg-secondary'],
                    'ADM' => ['label' => 'Administrador', 'class' => 'bg-danger'],
                    'SUPERADM' => ['label' => 'Proprietário', 'class' => 'bg-warning text-dark'],
                ];
                $roleKey = $_SESSION['role'] ?? 'USER';
                $roleInfo = $roleLabels[$roleKey];
                ?>
                <span class="badge <?= $roleInfo['class'] ?> mb-3"><?= htmlspecialchars($roleInfo['label']) ?></span>
            </div>
        </section>

        <!-- Informações e Detalhes -->
        <section class="info-section">
            <div class="row g-4">
                <!-- Informações Pessoais -->
                <div class="col-lg-6 animate-on-load fade-in-delay-1">
                    <div class="info-card h-100">
                        <div class="info-card-header">
                            <i class="info-card-icon fas fa-user"></i>
                            <h2 class="info-card-title">Informações Pessoais</h2>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Nome</div>
                            <div class="info-value"><?= htmlspecialchars($_SESSION['name'] ?? 'N/D') ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Username</div>
                            <div class="info-value">@<?= htmlspecialchars($_SESSION['user_name']) ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?= htmlspecialchars($_SESSION['user_email']) ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">País</div>
                            <div class="info-value"><?= htmlspecialchars($_SESSION['country'] ?? 'N/D') ?></div>
                        </div>

                        <?php if (!empty($_SESSION['city'])): ?>
                            <div class="info-item">
                                <div class="info-label">Cidade</div>
                                <div class="info-value"><?= htmlspecialchars($_SESSION['city']) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="info-item">
                            <div class="info-label">Data de Nasc.</div>
                            <div class="info-value">
                                <?php if (!empty($_SESSION['date_of_birth'])): ?>
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($_SESSION['date_of_birth']))) ?>
                                    (<?php
                                    $dob = new DateTime($_SESSION['date_of_birth']);
                                    $hoje = new DateTime('today');
                                    $idade = $dob->diff($hoje)->y;
                                    echo $idade . ' anos';
                                    ?>)
                                <?php else: ?>
                                    N/D
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Gênero</div>
                            <div class="info-value"><?= htmlspecialchars($_SESSION['genero'] ?? 'N/D') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Biografia e Atividade -->
                <div class="col-lg-6 animate-on-load fade-in-delay-2">
                    <div class="info-card h-100">
                        <div class="info-card-header">
                            <i class="info-card-icon fas fa-book"></i>
                            <h2 class="info-card-title">Biografia</h2>
                        </div>

                        <div class="bio-content">
                            <?= nl2br(htmlspecialchars($_SESSION['bio'] ?? 'Sem biografia.')) ?>
                        </div>

                        <div class="info-card-header mt-4">
                            <i class="info-card-icon fas fa-clock"></i>
                            <h2 class="info-card-title">Atividade da Conta</h2>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Conta criada</div>
                            <div class="info-value">
                                <i class="far fa-calendar-alt me-2"></i>
                                <?= date('d/m/Y', strtotime($_SESSION['created_at'])) ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Último acesso</div>
                            <div class="info-value">
                                <i class="far fa-clock me-2"></i>
                                <?php if (!empty($_SESSION['previous_login']) && $_SESSION['previous_login'] !== '0000-00-00 00:00:00'): ?>
                                    <?= date('d/m/Y H:i', strtotime($_SESSION['previous_login'])) ?>
                                <?php else: ?>
                                    Primeiro acesso
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Função</div>
                            <div class="info-value">
                                <span
                                    class="badge <?= $roleInfo['class'] ?>"><?= htmlspecialchars($roleInfo['label']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php
        // Apenas modificando a seção de redes sociais - o resto do arquivo permanece intacto
        if ($hasSocialMedia): ?>
            <!-- Redes Sociais -->
            <div class="col-12 animate-on-load fade-in-delay-3">
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="info-card-icon fas fa-share-alt"></i>
                        <h2 class="info-card-title">Redes Sociais</h2>
                    </div>

                    <div class="social-media-grid">
                        <?php
                        $socialFields = [
                            'WHATSAPP' => 'WhatsApp',
                            'YOUTUBE' => 'YouTube',
                            'INSTAGRAM' => 'Instagram',
                            'TIKTOK' => 'TikTok',
                            'FACEBOOK' => 'Facebook',
                            'LINKEDIN' => 'LinkedIn',
                            'GITHUB' => 'GitHub',
                            'TWITTER' => 'Twitter'
                        ];

                        foreach ($socialFields as $field => $label) {
                            if (!empty($_SESSION['socials'][$field]) && isValidUrl($_SESSION['socials'][$field])) {
                                $url = $_SESSION['socials'][$field];
                                // Adiciona http:// se não tiver protocolo
                                if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                                    $url = 'http://' . $url;
                                }

                                $icon = getSocialIcon($field);
                                $socialClass = 'social-' . strtolower($field);
                                ?>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="social-icon-link <?= $socialClass ?>"
                                    title="<?= htmlspecialchars($label) ?>">
                                    <div class="social-icon-container">
                                        <i class="<?= $icon ?>"></i>
                                    </div>
                                    <span><?= htmlspecialchars($label) ?></span>
                                </a>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['roblox_username'])):
            $robloxData = getRobloxUserData($_SESSION['roblox_username']);
            if ($robloxData): ?>
                <div class="col-12 animate-on-load fade-in-delay-4">
                    <div class="roblox-card">
                        <div class="roblox-header">
                            <i class="roblox-icon fas fa-gamepad"></i>
                            <h2 class="roblox-title">Perfil do Roblox</h2>
                        </div>

                        <div class="roblox-profile">
                            <img src="<?= htmlspecialchars($robloxData['avatarUrl']) ?>" alt="Avatar do Roblox"
                                class="roblox-avatar">

                            <div class="roblox-info">
                                <h3 class="roblox-username">@<?= htmlspecialchars($robloxData['username']) ?></h3>
                                <p class="roblox-displayname"><?= htmlspecialchars($robloxData['displayName']) ?></p>

                                <?php if (!empty($robloxData['description'])): ?>
                                    <p class="roblox-description"><?= nl2br(htmlspecialchars($robloxData['description'])) ?></p>
                                <?php endif; ?>

                                <div class="roblox-stats">
                                    <div class="roblox-stat">
                                        <div class="roblox-stat-label">Membro desde</div>
                                        <div class="roblox-stat-value">
                                            <?= date('d/m/Y', strtotime($robloxData['created'])) ?>
                                        </div>
                                    </div>
                                </div>

                                <a href="https://www.roblox.com/users/<?= $robloxData['id'] ?>/profile" target="_blank"
                                    rel="noopener noreferrer" class="roblox-link">
                                    Ver perfil no Roblox
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Botões de Ação -->
        <section class="actions-section animate-on-load fade-in-delay-3">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="editar-perfil.php" class="action-btn btn-kr">
                    <i class="fa fa-edit"></i> Editar Perfil
                </a>

                <a href="alterar-password.php" class="action-btn btn-secondary">
                    <i class="fa fa-lock"></i> Alterar Palavra-passe
                </a>

                <a href="estatisticas.php" class="action-btn btn-outline-warning">
                    <i class="fa fa-chart-line"></i> Estatísticas
                </a>

                <button class="action-btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fa fa-sign-out-alt"></i> Terminar Sessão
                </button>
            </div>
        </section>
    </main>

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
    <script src="../js/includes/profileMenu.js"></script>
    <script src="../js/Info_produtos.js"></script>
    <script src="../js/Gerais/Geral.js"></script>
    <script src="JS/perfil.js" defer></script>
    <script src="../js/Gerais/cookieConsent.js"></script>
    <script src="../js/Gerais/notification_API.js" defer></script>
</body>

</html>