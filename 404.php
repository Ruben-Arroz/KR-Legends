<?php
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();

date_default_timezone_set('Europe/Lisbon');
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
    <link rel="icon" type="image/png" sizes="32x32" href="/~a29621/KRLegends/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/~a29621/KRLegends/favicon_io/favicon-16x16.png">
    <link rel="shortcut icon" href="/~a29621/KRLegends/favicon_io/favicon.ico">

    <!-- Favicon para Android -->
    <link rel="icon" type="image/png" sizes="192x192" href="/~a29621/KRLegends/favicon_io/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/~a29621/KRLegends/favicon_io/android-chrome-512x512.png">

    <!-- Favicon para iOS (iPhone, iPad) -->
    <link rel="apple-touch-icon" sizes="180x180" href="/~a29621/KRLegends/favicon_io/apple-touch-icon.png">

    <!-- Manifesto para apps web em Android -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#000000">

    <title>KR Legends - Not Found</title>

    <!-- CSS de terceiros -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-somehashvalido1234567890abcdefghijklmnopqrstuvwxyz" crossorigin="anonymous"
        referrerpolicy="no-referrer">

    <!-- Seus estilos -->
    <link href="/~a29621/KRLegends/css/Geral.css" rel="stylesheet">
    <link href="/~a29621/KRLegends/css/cookieConsent.css" rel="stylesheet">
    <link href="/~a29621/KRLegends/css/includes/profileMenu.css" rel="stylesheet">
    <link href="/~a29621/KRLegends/css/includes/modal-logout.css" rel="stylesheet">
    <link href="/~a29621/KRLegends/css/not-found.css" rel="stylesheet">
    <link href="/~a29621/KRLegends/css/not-found-responsive.css" rel="stylesheet">
</head>

<body class="bg-black text-white">
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

    <!-- Sobreposição visual quando o mini carrinho estiver ativo -->
    <div class="overlay" id="overlay"></div>
    <!-- Header -->
    <header class="fixed-top bg-black bg-opacity-90 border-bottom border-warning border-opacity-25">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand logo" href="/~a29621/KRLegends/Index.php">
                    <img src="/~a29621/KRLegends/Imagens/Logotipos/Logo KR Legends - soft trans recortado.png"
                        alt="KR Legends" height="70">
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
                                <i class="bi bi-info-circle me-1"></i> Sobre
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="sobreDropdown">
                                <li><a class="dropdown-item d-flex align-items-center" href="/~a29621/KRLegends/Index.php">
                                        <i class="bi bi-house-door me-2"></i> Início
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="/~a29621/KRLegends/PAP.php">
                                        <i class="bi bi-journal-text me-2"></i> PAP
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="/~a29621/KRLegends/Equipa.php">
                                        <i class="bi bi-people me-2"></i> Equipa
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="/~a29621/KRLegends/Creditos.php">
                                        <i class="bi bi-award me-2"></i> Créditos
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="/~a29621/KRLegends/Politica.php">
                                        <i class="fas fa-vote-yea me-2"></i> Política
                                    </a></li>
                            </ul>
                        </li>

                        <!-- Comunidade & Social Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                                id="comSocialDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-people-fill me-1"></i> Comunidade & Social
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="comSocialDropdown">
                                <li><a class="dropdown-item d-flex align-items-center"
                                        href="/~a29621/KRLegends/Comunidade.php">
                                        <i class="bi bi-people me-2"></i> Comunidade
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="/~a29621/KRLegends/Suporte.php">
                                        <i class="bi bi-life-preserver me-2"></i> Suporte
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider bg-secondary">
                                </li>
                                <li><a class="dropdown-item d-flex align-items-center"
                                        href="https://www.roblox.com/pt/communities/36061138" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            width="16" height="16" class="me-2">
                                            <title>Roblox</title>
                                            <path
                                                d="M5.164 0 .16 18.928 18.836 24 23.84 5.072Zm8.747 15.354-5.219-1.417 1.399-5.29 5.22 1.418-1.4 5.29z" />
                                        </svg>
                                        Roblox Group
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center"
                                        href="https://www.youtube.com/@KRLegends-media" target="_blank">
                                        <i class="bi bi-youtube me-2"></i> YouTube
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center"
                                        href="https://www.instagram.com/kr_legends.oficial/" target="_blank">
                                        <i class="bi bi-instagram me-2"></i> Instagram
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center"
                                        href="https://www.tiktok.com/@kr.legends?lang=pt-BR" target="_blank">
                                        <i class="bi bi-tiktok me-2"></i> TikTok
                                    </a></li>
                            </ul>
                        </li>

                        <!-- Jogo Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="jogoDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-controller me-1"></i> Jogo
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="jogoDropdown">
                                <li><a class="dropdown-item d-flex align-items-center"
                                        href="/~a29621/KRLegends/Atualizacoes.php">
                                        <i class="bi bi-bell me-2"></i> Atualizações
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center"
                                        href="/~a29621/KRLegends/modos-de-jogo.php">
                                        <i class="bi bi-joystick me-2"></i> Modos de Jogo
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="/~a29621/KRLegends/Rankings.php">
                                        <i class="bi bi-bar-chart-line me-2"></i> Rankings
                                    </a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="/~a29621/KRLegends/Galeria.php">
                                        <i class="bi bi-images me-2"></i> Galeria
                                    </a></li>
                            </ul>
                        </li>

                        <!-- Loja -->
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="/~a29621/KRLegends/Store.php">
                                <i class="bi bi-shop me-1"></i> Loja Online
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
                        <?php if (isset($_SESSION['user_email'])): ?>
                            <!-- Modal de Logout -->
                            <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel"
                                aria-hidden="true">
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
                                            <button type="button" class="btn btn-outline-light logout-btn-cancel"
                                                data-bs-dismiss="modal">Cancelar</button>
                                            <form action="/~a29621/KRLegends/admin/PHP/logout.php" method="POST"
                                                style="display:inline-block;">
                                                <button type="submit" class="btn logout-btn-confirm-logout">Terminar
                                                    Sessão</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="profile-menu-container">
                                <a href="/~a29621/KRLegends/Utilizador/perfil.php" class="profile-name" title="Ver perfil">
                                    <?php
                                    $displayName = !empty($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['user_name'];
                                    echo htmlspecialchars($displayName);
                                    ?>
                                </a>
                                <div class="dropdown">
                                    <a class="profile-avatar-trigger" href="#" id="userMenu" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false" title="Menu do utilizador">
                                        <img src="/~a29621/KRLegends/Imagens/avatares/<?= htmlspecialchars($_SESSION['user_avatar']) ?>"
                                            alt="Avatar do utilizador" class="rounded-circle">
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end"
                                        aria-labelledby="userMenu">
                                        <li><a class="dropdown-item d-flex align-items-center"
                                                href="/~a29621/KRLegends/Utilizador/perfil.php">
                                                <i class="bi bi-person-circle me-2"></i> Perfil
                                            </a></li>
                                        <li><a class="dropdown-item d-flex align-items-center"
                                                href="/~a29621/KRLegends/Utilizador/stats.php">
                                                <i class="bi bi-bar-chart-line me-2"></i> Estatísticas
                                            </a></li>
                                        <li><a class="dropdown-item d-flex align-items-center"
                                                href="/~a29621/KRLegends/Utilizador/settings.php">
                                                <i class="bi bi-gear me-2"></i> Definições
                                            </a></li>
                                        <li><a class="dropdown-item d-flex align-items-center"
                                                href="/~a29621/KRLegends/Suporte.php">
                                                <i class="bi bi-question-circle me-2"></i> Ajuda & FAQ
                                            </a></li>
                                        <li><a class="dropdown-item d-flex align-items-center"
                                                href="/~a29621/KRLegends/Politica.php">
                                                <i class="bi bi-file-earmark-text me-2"></i> Termos & Políticas
                                            </a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <button class="dropdown-item d-flex align-items-center text-danger"
                                                data-bs-toggle="modal" data-bs-target="#logoutModal">
                                                <i class="bi bi-box-arrow-right me-2"></i> Terminar Sessão
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <?php
                                if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['ADM', 'SUPERADM'], true)):
                                    $iconClass = $_SESSION['role'] === 'SUPERADM' ? 'fa-solid fa-gem' : 'fa-solid fa-user-tie';
                                    $ariaLabel = $_SESSION['role'] === 'SUPERADM' ? 'Área Super Admin' : 'Área Admin';
                                    $adminUrl = '/~a29621/KRLegends/admin/modoADM.php';
                                    ?>
                                    <a href="<?= htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8') ?>" class="profile-admin-icon"
                                        title="<?= htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') ?>"
                                        aria-label="<?= htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="<?= $iconClass ?>"></i>
                                    </a>
                                <?php endif; ?>

                            </div>
                        <?php else: ?>
                            <a href="/~a29621/KRLegends/Login-Cadastro/Login.php" class="btn auth-btn auth-btn-login">
                                <i class="bi bi-person"></i> Entrar
                            </a>
                            <a href="/~a29621/KRLegends/Login-Cadastro/Cadastro.php" class="btn auth-btn auth-btn-register">
                                <i class="bi bi-person-plus"></i> Criar Conta
                            </a>
                        <?php endif; ?>
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

    <!-- Main Content - Página 404 Redesenhada -->
    <main class="notfnd-container">
        <!-- Elementos de fundo animados -->
        <div class="notfnd-racing-lines">
            <div class="notfnd-line notfnd-line-1"></div>
            <div class="notfnd-line notfnd-line-2"></div>
            <div class="notfnd-line notfnd-line-3"></div>
            <div class="notfnd-line notfnd-line-4"></div>
            <div class="notfnd-line notfnd-line-5"></div>
        </div>

        <!-- Elementos flutuantes -->
        <div class="notfnd-floating-elements">
            <div class="notfnd-floating-car">
                <i class="fas fa-car-side"></i>
            </div>
            <div class="notfnd-floating-spark notfnd-spark-1">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="notfnd-floating-spark notfnd-spark-2">
                <i class="fas fa-star"></i>
            </div>
            <div class="notfnd-floating-spark notfnd-spark-3">
                <i class="fas fa-fire"></i>
            </div>
            <div class="notfnd-floating-spark notfnd-spark-4">
                <i class="fas fa-gem"></i>
            </div>
        </div>

        <!-- Partículas de fundo -->
        <div class="notfnd-particles">
            <div class="notfnd-particle"></div>
            <div class="notfnd-particle"></div>
            <div class="notfnd-particle"></div>
            <div class="notfnd-particle"></div>
            <div class="notfnd-particle"></div>
            <div class="notfnd-particle"></div>
        </div>

        <!-- Container principal -->
        <div class="notfnd-main-wrapper">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10 col-xl-8">

                        <!-- Container de erro principal -->
                        <div class="notfnd-error-container">

                            <!-- Seção do código de erro -->
                            <div class="notfnd-error-code-section">
                                <div class="notfnd-error-code" data-text="404">404</div>
                                <div class="notfnd-error-subtitle">
                                    <i class="fas fa-exclamation-triangle notfnd-warning-icon"></i>
                                    <span>PÁGINA NÃO ENCONTRADA</span>
                                </div>
                            </div>

                            <!-- Conteúdo principal -->
                            <div class="notfnd-error-content">
                                <h1 class="notfnd-error-title">Ops! Saíste da pista!</h1>
                                <p class="notfnd-error-description">
                                    Parece que tomaste uma curva errada e acabaste fora do circuito principal do KR
                                    Legends.
                                    A página que procuras pode ter sido movida, removida ou nunca existiu.
                                </p>
                            </div>

                            <!-- Detalhes técnicos do erro -->
                            <div class="notfnd-error-details">
                                <h3 class="notfnd-details-title">
                                    <i class="fas fa-info-circle"></i>
                                    Detalhes Técnicos
                                </h3>
                                <div class="notfnd-detail-grid">
                                    <div class="notfnd-detail-item">
                                        <span class="notfnd-detail-label">Código do Erro:</span>
                                        <span class="notfnd-detail-value">404 - Not Found</span>
                                    </div>
                                    <div class="notfnd-detail-item">
                                        <span class="notfnd-detail-label">URL Solicitado:</span>
                                        <span class="notfnd-detail-value"
                                            id="notfnd-current-url"><?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div class="notfnd-detail-item">
                                        <span class="notfnd-detail-label">Timestamp(Lisbon):</span>
                                        <span class="notfnd-detail-value"><?= date('d/m/Y H:i:s') ?></span>
                                    </div>
                                    <div class="notfnd-detail-item">
                                        <span class="notfnd-detail-label">Referrer:</span>
                                        <span
                                            class="notfnd-detail-value"><?= htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'Direto', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de ação -->
                            <div class="notfnd-action-buttons">
                                <button onclick="window.location.href='/~a29621/KRLegends/Index.php'"
                                    class="notfnd-btn notfnd-btn-primary">
                                    <i class="fas fa-home"></i>
                                    <span>Voltar ao Início</span>
                                </button>
                                <button onclick="history.back()" class="notfnd-btn notfnd-btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Página Anterior</span>
                                </button>
                                <button onclick="document.getElementById('notfnd-search-modal').style.display='flex'"
                                    class="notfnd-btn notfnd-btn-tertiary">
                                    <i class="fas fa-search"></i>
                                    <span>Procurar</span>
                                </button>
                            </div>

                            <!-- Seção de ajuda -->
                            <div class="notfnd-help-section">
                                <h3 class="notfnd-help-title">
                                    <i class="fas fa-life-ring"></i>
                                    Como podemos ajudar?
                                </h3>
                                <div class="notfnd-help-grid">
                                    <a href="/~a29621/KRLegends/Index.php" class="notfnd-help-link">
                                        <i class="fas fa-home"></i>
                                        <div class="notfnd-help-content">
                                            <span class="notfnd-help-link-title">Página Inicial</span>
                                            <span class="notfnd-help-link-desc">Voltar ao início do KR Legends</span>
                                        </div>
                                    </a>
                                    <a href="/~a29621/KRLegends/Store.php" class="notfnd-help-link">
                                        <i class="fas fa-shopping-cart"></i>
                                        <div class="notfnd-help-content">
                                            <span class="notfnd-help-link-title">Loja Online</span>
                                            <span class="notfnd-help-link-desc">Produtos e merchandise</span>
                                        </div>
                                    </a>
                                    <a href="/~a29621/KRLegends/Comunidade.php" class="notfnd-help-link">
                                        <i class="fas fa-users"></i>
                                        <div class="notfnd-help-content">
                                            <span class="notfnd-help-link-title">Comunidade</span>
                                            <span class="notfnd-help-link-desc">Junta-te à nossa comunidade</span>
                                        </div>
                                    </a>
                                    <a href="/~a29621/KRLegends/Suporte.php" class="notfnd-help-link">
                                        <i class="fas fa-headset"></i>
                                        <div class="notfnd-help-content">
                                            <span class="notfnd-help-link-title">Suporte</span>
                                            <span class="notfnd-help-link-desc">Precisa de ajuda? Contacte-nos</span>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <!-- Citação temática -->
                            <div class="notfnd-racing-quote">
                                <blockquote>
                                    "Na corrida da vida, às vezes tomamos a curva errada. O importante é saber como
                                    voltar à pista principal."
                                </blockquote>
                                <cite>— Equipa KR Legends</cite>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicador de status -->
        <div class="notfnd-status-indicator">
            <div class="notfnd-status-light notfnd-status-error"></div>
            <span>Erro 404 - Página não encontrada</span>
        </div>

    </main>

    <!-- Modal de Pesquisa -->
    <div id="notfnd-search-modal" class="notfnd-search-modal">
        <div class="notfnd-search-modal-content">
            <div class="notfnd-search-modal-header">
                <h3>
                    <i class="fas fa-search"></i>
                    Procurar no KR Legends
                </h3>
                <button onclick="document.getElementById('notfnd-search-modal').style.display='none'"
                    class="notfnd-search-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="notfnd-search-modal-body">
                <form id="notfnd-search-form" onsubmit="return handleSearch(event)">
                    <div class="notfnd-search-input-group">
                        <input type="text" id="notfnd-search-input" placeholder="O que está à procura?" required>
                        <button type="submit" class="notfnd-search-submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                <div class="notfnd-search-suggestions">
                    <h4>Sugestões populares:</h4>
                    <div class="notfnd-search-tags">
                        <span class="notfnd-search-tag" onclick="searchFor('loja')">Loja</span>
                        <span class="notfnd-search-tag" onclick="searchFor('comunidade')">Comunidade</span>
                        <span class="notfnd-search-tag" onclick="searchFor('rankings')">Rankings</span>
                        <span class="notfnd-search-tag" onclick="searchFor('galeria')">Galeria</span>
                        <span class="notfnd-search-tag" onclick="searchFor('suporte')">Suporte</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-black py-5 border-top border-warning border-opacity-25">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-6">
                    <img src="/~a29621/KRLegends/Imagens/Logotipos/Logo KR Legends - soft trans recortado.png"
                        alt="KR Legends" height="65" class="mb-3">
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
                <p class="text-white-50 mb-2" data-translate="footer.rights">
                    ©2026 KR Legends. Todos os direitos reservados.
                </p>
                <p class="text-white-50 small" data-translate="footer.developed">
                    Desenvolvido pela equipa de KR Legends com paixão pelo automobilismo e programação.
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts de terceiros -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Seus scripts -->
    <script src="/~a29621/KRLegends/js/not-found.js"></script>
    <script src="/~a29621/KRLegends/js/includes/profileMenu.js"></script>
    <script src="/~a29621/KRLegends/js/Info_produtos.js"></script>
    <script src="/~a29621/KRLegends/js/Gerais/Geral.js"></script>
    <script src="/~a29621/KRLegends/js/Gerais/cookieConsent.js"></script>

</body>

</html>