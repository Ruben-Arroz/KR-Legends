<?php
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <!-- ========== METADADOS BÁSICOS ========== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <!-- Favicon padrão (navegadores desktop) -->
    <link rel="icon" type="image/png" sizes="32x32" href="favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon_io/favicon-16x16.png">
    <link rel="shortcut icon" href="favicon_io/favicon.ico">

    <!-- Favicon para Android -->
    <link rel="icon" type="image/png" sizes="192x192" href="favicon_io/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="favicon_io/android-chrome-512x512.png">

    <!-- Favicon para iOS (iPhone, iPad) -->
    <link rel="apple-touch-icon" sizes="180x180" href="favicon_io/apple-touch-icon.png">

    <!-- Manifesto para apps web em Android -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#000000">

    <!-- ========== TÍTULO DA PÁGINA ========== -->
    <title>KR Legends - Loja Online</title>

    <!-- ========== FONTE E FRAMEWORKS EXTERNOS (CDNs) ========== -->
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Ícones do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome (ícones adicionais) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- ========== FICHEIROS CSS PERSONALIZADOS ========== -->
    <link href="css/cookieConsent.css" rel="stylesheet">
    <link href="css/Geral_Store.css" rel="stylesheet">
    <link href="css/Produtos.css" rel="stylesheet">
    <link href="css/Geral.css" rel="stylesheet">
    <link href="css/includes/profileMenu.css" rel="stylesheet">
    <link href="css/includes/modal-logout.css" rel="stylesheet">
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

    <!-- Modal de Stock -->
    <div id="stock-alert-modal" class="modal-custom">
        <div class="modal-content">
            <h2 id="stock-alert-title">Alerta</h2>
            <p id="stock-alert-message">Mensagem dinâmica aqui</p>
            <button id="stock-alert-ok">OK</button>
        </div>
    </div>

    <!-- Header -->
    <header class="fixed-top bg-black bg-opacity-90 border-bottom border-warning border-opacity-25">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" class="logo" href="Index.php">
                    <img src="Imagens/Logotipos/Logo KR Legends - soft trans recortado.png" alt="KR Legends"
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
                                    <a class="dropdown-item d-flex align-items-center" href="Index.php">
                                        <i class="bi bi-house-door me-2" aria-hidden="true"></i> Início
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="PAP.php">
                                        <i class="bi bi-journal-text me-2" aria-hidden="true"></i> PAP
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="Equipa.php">
                                        <i class="bi bi-people me-2" aria-hidden="true"></i> Equipa
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="Creditos.php">
                                        <i class="bi bi-award me-2" aria-hidden="true"></i> Créditos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="Politica.php">
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
                                    <a class="dropdown-item d-flex align-items-center" href="Comunidade.php">
                                        <i class="bi bi-people me-2" aria-hidden="true"></i> Comunidade
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="Suporte.php">
                                        <i class="bi bi-life-preserver me-2" aria-hidden="true"></i> Suporte
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider bg-secondary" />
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="https://www.roblox.com/pt/communities/36061138" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            width="16" height="16" class="me-2" aria-hidden="true">
                                            <title>Roblox</title>
                                            <path
                                                d="M5.164 0 .16 18.928 18.836 24 23.84 5.072Zm8.747 15.354-5.219-1.417 1.399-5.29 5.22 1.418-1.4 5.29z" />
                                        </svg>
                                        Roblox Group
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="https://www.youtube.com/@KRLegends-media" target="_blank">
                                        <i class="bi bi-youtube me-2" aria-hidden="true"></i> YouTube
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="https://www.instagram.com/kr_legends.oficial/" target="_blank">
                                        <i class="bi bi-instagram me-2" aria-hidden="true"></i> Instagram
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="https://www.tiktok.com/@kr.legends?lang=pt-BR" target="_blank">
                                        <i class="bi bi-tiktok me-2" aria-hidden="true"></i> TikTok
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
                                    <a class="dropdown-item d-flex align-items-center" href="Atualizacoes.php">
                                        <i class="bi bi-bell me-2" aria-hidden="true"></i> Atualizações
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="modos-de-jogo.php">
                                        <i class="bi bi-joystick me-2" aria-hidden="true"></i> Modos de Jogo
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="Rankings.php">
                                        <i class="bi bi-bar-chart-line me-2" aria-hidden="true"></i> Rankings
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="Galeria.php">
                                        <i class="bi bi-images me-2" aria-hidden="true"></i> Galeria
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Loja -->
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="Store.php">
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
                        <?php include __DIR__ . '/includes/profileMenu.php'; ?>
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


    <main class="main">
        <div class="container">
            <h1 class="main-title">KR Legends Store</h1>

            <div class="product-detail">
                <div class="product-gallery">
                    <div class="thumbnails">
                        <!-- Thumbnails will be inserted here by JavaScript -->
                    </div>
                    <div class="main-image">
                        <!-- Main image will be inserted here by JavaScript -->
                    </div>
                </div>

                <div class="product-info">
                    <h2 class="product-model"></h2>
                    <h3 class="product-name"></h3>

                    <div class="price-date">
                        <span class="product-price"></span>
                        <span class="product-date"></span>
                    </div>

                    <div class="product-details">
                        <p class="detail-item">
                            <span>Estoque:</span>
                            <span class="stock-value"></span>
                        </p>
                        <p class="detail-item">
                            <span>Cor:</span>
                            <span class="color-value"></span>
                        </p>
                        <p class="detail-item">
                            <span>Gênero:</span>
                            <span class="gender-value"></span>
                        </p>
                    </div>

                    <div class="product-description">
                        <h4>Descrição</h4>
                        <p></p>
                    </div>

                    <div class="size-selection">
                        <h4>Tamanhos</h4>
                        <div class="size-buttons">
                            <!-- Size buttons will be inserted here by JavaScript -->
                        </div>
                    </div>

                    <button class="add-to-cart-btn">
                        <span class="cart-icon"></span>
                        Adicionar ao carrinho
                    </button>

                    <div class="shipping-info">
                        <p class="shipping-text"></p>
                        <p class="care-instructions"></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- ========== RODAPÉ ========== -->
    <footer class="bg-black py-5 border-top border-warning border-opacity-25">
        <div class="container">
            <div class="row mb-4">
                <!-- Logotipo e descrição -->
                <div class="col-md-6">
                    <img src="Imagens/Logotipos/Logo KR Legends - soft trans recortado.png" alt="KR Legends" height="65"
                        class="mb-3">
                    <p class="text-white-50" data-translate="footer.joinCommunity">
                        Junte-se à comunidade KR Legends e faça parte desta revolução no mundo das corridas virtuais.
                    </p>
                </div>

                <!-- Redes sociais -->
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

            <!-- Direitos e créditos -->
            <div class="text-center border-top border-warning border-opacity-25 pt-4">
                <p class="text-white-50 mb-2" data-translate="footer.rights">©2026 KR Legends. Todos os direitos
                    reservados.</p>
                <p class="text-white-50 small" data-translate="footer.developed">
                    Desenvolvido pela equipa de KR Legends com paixão pelo automobilismo e programação.
                </p>
            </div>
        </div>
    </footer>

    <!-- ========== SCRIPTS EXTERNOS E PERSONALIZADOS ========== -->
    <!-- Bootstrap JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

    <!-- Scripts customizados -->
    <script src="js/Gerais/cookieConsent.js"></script>
    <script src="js/Info_produtos.js"></script>
    <script src="js/Produtos.js"></script>
    <script src="js/Geral_Store.js" defer></script>
    <script src="js/Gerais/Geral.js"></script>
    <script src="js/Gerais/notification_API.js" defer></script>
</body>

</html>