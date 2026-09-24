<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();
require_once __DIR__ . '/../../databaseconnect.php';
// Verificar permissão
if (!in_array($_SESSION['role'], ['ADM', 'SUPERADM'])) {
    http_response_code(403);
    die('Acesso negado');
}

// Buscar estatísticas dos utilizadores
try {
    // Total de utilizadores
    $totalUsersQuery = "SELECT COUNT(*) as total FROM KR_USERS";
    $totalUsersResult = $mysqli->query($totalUsersQuery);
    $totalUsers = $totalUsersResult->fetch_assoc()['total'];

    // Utilizadores registados hoje
    $todayUsersQuery = "SELECT COUNT(*) as today FROM KR_USERS WHERE DATE(KR_CREATED_AT) = CURDATE()";
    $todayUsersResult = $mysqli->query($todayUsersQuery);
    $todayUsers = $todayUsersResult->fetch_assoc()['today'];

    // Utilizadores registados esta semana
    $weekUsersQuery = "SELECT COUNT(*) as week FROM KR_USERS WHERE YEARWEEK(KR_CREATED_AT, 1) = YEARWEEK(CURDATE(), 1)";
    $weekUsersResult = $mysqli->query($weekUsersQuery);
    $weekUsers = $weekUsersResult->fetch_assoc()['week'];

    // Utilizadores registados este mês
    $monthUsersQuery = "SELECT COUNT(*) as month FROM KR_USERS WHERE YEAR(KR_CREATED_AT) = YEAR(CURDATE()) AND MONTH(KR_CREATED_AT) = MONTH(CURDATE())";
    $monthUsersResult = $mysqli->query($monthUsersQuery);
    $monthUsers = $monthUsersResult->fetch_assoc()['month'];

    // Utilizadores ativos (com login nos últimos 7 dias)
    $activeUsersQuery = "SELECT COUNT(*) as active FROM KR_USERS WHERE KR_LAST_LOGIN >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $activeUsersResult = $mysqli->query($activeUsersQuery);
    $activeUsers = $activeUsersResult->fetch_assoc()['active'];

    // Utilizadores por género
    $genderStatsQuery = "SELECT KR_GENERO, COUNT(*) as count FROM KR_USERS GROUP BY KR_GENERO";
    $genderStatsResult = $mysqli->query($genderStatsQuery);
    $genderStats = [];
    while ($row = $genderStatsResult->fetch_assoc()) {
        $genderStats[$row['KR_GENERO']] = $row['count'];
    }

    // Utilizadores por país (top 5)
    $countryStatsQuery = "SELECT KR_COUNTRY, COUNT(*) as count FROM KR_USERS WHERE KR_COUNTRY != 'Não definido' GROUP BY KR_COUNTRY ORDER BY count DESC LIMIT 5";
    $countryStatsResult = $mysqli->query($countryStatsQuery);
    $countryStats = [];
    while ($row = $countryStatsResult->fetch_assoc()) {
        $countryStats[] = $row;
    }

} catch (Exception $e) {
    // Em caso de erro, definir valores padrão
    $totalUsers = 0;
    $todayUsers = 0;
    $weekUsers = 0;
    $monthUsers = 0;
    $activeUsers = 0;
    $genderStats = [];
    $countryStats = [];
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

    <title>KR Legends - Modo ADM</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../css/Geral.css" rel="stylesheet">
    <link href="../css/cookieConsent.css" rel="stylesheet">
    <link href="../css/includes/profileMenu.css" rel="stylesheet">
    <link href="../css/includes/modal-logout.css" rel="stylesheet">
    <link href="CSS/geral-adm.css" rel="stylesheet">
    <link href="CSS/modoADM.css" rel="stylesheet">
    <link href="CSS/modoADM-responsive.css" rel="stylesheet">
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
                                        <a class="dropdown-item d-flex align-items-center"
                                            href="../Utilizador/perfil.php">
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

        <!-- ==================================
       CONTEÚDO PRINCIPAL (Dashboard) 
       ================================== -->
        <main class="adm-main-content">
            <!-- Seção Hero de Boas-Vindas -->
            <section class="adm-hero">
                <div class="adm-hero-content">
                    <div class="adm-hero-text">
                        <div class="adm-hero-badge">
                            <i class="bi bi-shield-check"></i>
                            Painel Administrativo
                        </div>
                        <h1 class="adm-hero-title">
                            Bem-vindo, <?= htmlspecialchars($_SESSION['name'] ?: $_SESSION['user_name']) ?>
                        </h1>
                        <p class="adm-hero-subtitle">
                            Controle total sobre a plataforma KR Legends. Gerir utilizadores, conteúdo e configurações
                            de forma segura e eficiente. Todas as ferramentas administrativas ao seu alcance.
                        </p>
                        <div class="adm-hero-actions">
                            <a href="users-adm.php" class="adm-btn adm-btn-primary">
                                <i class="bi bi-people-fill me-2"></i>
                                Gerir Utilizadores
                            </a>
                            <a href="equipa-adm.php" class="adm-btn adm-btn-secondary">
                                <i class="bi bi-gear me-2"></i>
                                Configurações
                            </a>
                        </div>
                    </div>
                    <div class="adm-hero-stats">
                        <div class="adm-hero-stat">
                            <div class="adm-hero-stat-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="adm-hero-stat-content">
                                <span class="adm-hero-stat-number"><?= number_format($totalUsers) ?></span>
                                <span class="adm-hero-stat-label">Total Utilizadores</span>
                            </div>
                        </div>
                        <div class="adm-hero-stat">
                            <div class="adm-hero-stat-icon">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div class="adm-hero-stat-content">
                                <span class="adm-hero-stat-number"><?= number_format($activeUsers) ?></span>
                                <span class="adm-hero-stat-label">Utilizadores Ativos</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="adm-hero-background"></div>
            </section>

            <!-- Seção de Estatísticas dos Utilizadores -->
            <section class="adm-stats">
                <div class="adm-stats-header">
                    <h2 class="adm-section-title">
                        <i class="bi bi-graph-up me-2"></i>
                        Estatísticas dos Utilizadores
                    </h2>
                    <p class="adm-section-subtitle">
                        Acompanhe o crescimento e atividade da comunidade KR Legends em tempo real
                    </p>
                </div>

                <!-- Cards de Estatísticas Principais -->
                <div class="adm-stats-grid">
                    <div class="adm-stat-card adm-stat-card-primary">
                        <div class="adm-stat-card-icon">
                            <i class="bi bi-calendar-day"></i>
                        </div>
                        <div class="adm-stat-card-content">
                            <h3 class="adm-stat-card-number"><?= $todayUsers ?></h3>
                            <p class="adm-stat-card-label">Registos Hoje</p>
                            <div class="adm-stat-card-trend">
                                <i class="bi bi-arrow-up"></i>
                                <span>Novos membros</span>
                            </div>
                        </div>
                    </div>

                    <div class="adm-stat-card adm-stat-card-success">
                        <div class="adm-stat-card-icon">
                            <i class="bi bi-calendar-week"></i>
                        </div>
                        <div class="adm-stat-card-content">
                            <h3 class="adm-stat-card-number"><?= $weekUsers ?></h3>
                            <p class="adm-stat-card-label">Registos Esta Semana</p>
                            <div class="adm-stat-card-trend">
                                <i class="bi bi-arrow-up"></i>
                                <span>Crescimento semanal</span>
                            </div>
                        </div>
                    </div>

                    <div class="adm-stat-card adm-stat-card-info">
                        <div class="adm-stat-card-icon">
                            <i class="bi bi-calendar-month"></i>
                        </div>
                        <div class="adm-stat-card-content">
                            <h3 class="adm-stat-card-number"><?= $monthUsers ?></h3>
                            <p class="adm-stat-card-label">Registos Este Mês</p>
                            <div class="adm-stat-card-trend">
                                <i class="bi bi-arrow-up"></i>
                                <span>Crescimento mensal</span>
                            </div>
                        </div>
                    </div>

                    <div class="adm-stat-card adm-stat-card-warning">
                        <div class="adm-stat-card-icon">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div class="adm-stat-card-content">
                            <h3 class="adm-stat-card-number"><?= $activeUsers ?></h3>
                            <p class="adm-stat-card-label">Utilizadores Ativos</p>
                            <div class="adm-stat-card-trend">
                                <i class="bi bi-clock"></i>
                                <span>Últimos 7 dias</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas Detalhadas -->
                <div class="adm-detailed-stats">
                    <!-- Estatísticas por Género -->
                    <div class="adm-stat-detail-card">
                        <div class="adm-stat-detail-header">
                            <h3 class="adm-stat-detail-title">
                                <i class="bi bi-gender-ambiguous me-2"></i>
                                Distribuição por Género
                            </h3>
                        </div>
                        <div class="adm-stat-detail-content">
                            <?php if (!empty($genderStats)): ?>
                                <?php foreach ($genderStats as $gender => $count): ?>
                                    <div class="adm-stat-item">
                                        <div class="adm-stat-item-info">
                                            <span class="adm-stat-item-label"><?= htmlspecialchars($gender) ?></span>
                                            <span class="adm-stat-item-value"><?= $count ?></span>
                                        </div>
                                        <div class="adm-stat-item-bar">
                                            <div class="adm-stat-item-progress"
                                                style="width: <?= $totalUsers > 0 ? ($count / $totalUsers) * 100 : 0 ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="adm-stat-no-data">Sem dados disponíveis</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Top Países -->
                    <div class="adm-stat-detail-card">
                        <div class="adm-stat-detail-header">
                            <h3 class="adm-stat-detail-title">
                                <i class="bi bi-globe me-2"></i>
                                Top 5 Países
                            </h3>
                        </div>
                        <div class="adm-stat-detail-content">
                            <?php if (!empty($countryStats)): ?>
                                <?php foreach ($countryStats as $index => $country): ?>
                                    <div class="adm-stat-item">
                                        <div class="adm-stat-item-info">
                                            <span class="adm-stat-item-rank">#<?= $index + 1 ?></span>
                                            <span
                                                class="adm-stat-item-label"><?= htmlspecialchars($country['KR_COUNTRY']) ?></span>
                                            <span class="adm-stat-item-value"><?= $country['count'] ?></span>
                                        </div>
                                        <div class="adm-stat-item-bar">
                                            <div class="adm-stat-item-progress"
                                                style="width: <?= $totalUsers > 0 ? ($country['count'] / $totalUsers) * 100 : 0 ?>%">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="adm-stat-no-data">Sem dados disponíveis</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="adm-quick-actions">
                    <h3 class="adm-quick-actions-title">
                        <i class="bi bi-lightning me-2"></i>
                        Ações Rápidas
                    </h3>
                    <div class="adm-quick-actions-grid">
                        <a href="users-adm.php" class="adm-quick-action">
                            <div class="adm-quick-action-icon">
                                <i class="bi bi-person-lines-fill"></i>
                            </div>
                            <div class="adm-quick-action-content">
                                <h4 class="adm-quick-action-title">Gerir Utilizadores</h4>
                                <p class="adm-quick-action-desc">Ver, editar e gerir contas de utilizadores</p>
                            </div>
                        </a>

                        <a href="equipa-adm.php" class="adm-quick-action">
                            <div class="adm-quick-action-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="adm-quick-action-content">
                                <h4 class="adm-quick-action-title">Gerir Equipa</h4>
                                <p class="adm-quick-action-desc">Adicionar e editar membros da equipa</p>
                            </div>
                        </a>

                        <a href="#" class="adm-quick-action disabled">
                            <div class="adm-quick-action-icon">
                                <i class="bi bi-cart-fill"></i>
                            </div>
                            <div class="adm-quick-action-content">
                                <h4 class="adm-quick-action-title">Loja Online</h4>
                                <p class="adm-quick-action-desc">Gerir produtos e vendas (Em breve)</p>
                            </div>
                        </a>

                        <a href="#" class="adm-quick-action disabled">
                            <div class="adm-quick-action-icon">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div class="adm-quick-action-content">
                                <h4 class="adm-quick-action-title">Logs do Sistema</h4>
                                <p class="adm-quick-action-desc">Visualizar atividades e logs (Em breve)</p>
                            </div>
                        </a>
                    </div>
                </div>
            </section>
        </main>
        <!-- FIM DO CONTEÚDO PRINCIPAL -->
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
    <script src="JS/modoADM.js"></script>
    <script src="../js/includes/profileMenu.js"></script>
    <script src="../js/Gerais/Geral.js"></script>
    <script src="../js/Gerais/cookieConsent.js"></script>
    <script src="../js/Gerais/notification_API.js" defer></script>
</body>

</html>