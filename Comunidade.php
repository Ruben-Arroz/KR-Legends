<?php
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();
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

    <title>KR Legends - Comunidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link href="css/Comunidade.css" rel="stylesheet">
    <link href="css/Geral.css" rel="stylesheet">
    <link href="css/cookieConsent.css" rel="stylesheet">
    <link href="css/Extras/kr_widget.css" rel="stylesheet">
    <link href="css/Extras/discord-widget.css" rel="stylesheet">
    <link href="css/Extras/discord-widget-responsive.css" rel="stylesheet">
    <link href="css/includes/profileMenu.css" rel="stylesheet">
    <link href="css/includes/modal-logout.css" rel="stylesheet">
    <link href="css/includes/modal-comunidade-feedback.css" rel="stylesheet">
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
    <!--
        HERO SECTION:
        Seção principal com fundo dinâmico, sobreposição, título, subtítulo, botão de ação e controles de carrossel.
    -->
    <section id="hero" class="min-vh-100 d-flex align-items-center text-center position-relative overflow-hidden">
        <div class="hero-overlay"></div>
        <div class="container position-relative hero-content">
            <!-- Título de boas-vindas com animação -->
            <h1 class="display-2 fw-bold text-warning mb-4 fade-in" data-translate="hero.welcome">
                A comunidade de KR Legends
            </h1>
            <!-- Subtítulo com efeito de slide -->
            <p class="lead text-white mb-5 mx-auto slide-up" style="max-width: 600px;" data-translate="hero.subtitle">
                A comunidade <b>KR Legends</b> não é apenas feita de jogadores, é feita de criadores, pilotos e fãs que
                vivem a experiência ao máximo. Junta-te a nós nesta corrida que nunca pára.
            </p>
            <!-- Botão de ação para jogar, com efeito bounce -->
            <a href="https://www.roblox.com/pt/games/113586179382037/KR-Legends-Beta" target="_blank"
                class="btn btn-warning btn-lg px-5 py-3 fw-bold bounce">
                <i class="bi bi-controller me-2"></i>
                <span data-translate="hero.playNow">Jogar Agora</span>
            </a>
            <!-- Controles do carrossel -->
            <div class="carousel-nav">
                <button class="carousel-btn" onclick="prevImage()">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="carousel-btn" onclick="nextImage()">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>

    <!--
        VIDEO SECTION:
        Seção de vídeo com sobreposição, partículas, título e iframe para exibição do vídeo.
    -->
    <section class="video-section position-relative py-5">
        <div class="video-bg-overlay"></div>
        <div class="particles-container"></div>
        <div class="container position-relative">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <div class="title-wrapper">
                            <h2 class="video-title display-4 text-warning fw-bold mb-4 animate-title"
                                data-translate="hero.welcome">
                                O KR Legends está no Youtube!
                                <div class="title-decoration"></div>
                            </h2>
                        </div>
                        <p class="video-subtitle lead text-light mb-5" data-translate="hero.subtitle">
                            Confere os melhores momentos do nosso canal e da comunidade
                            <span class="text-warning fw-bold position-relative highlight-text"><a
                                    href="https://www.youtube.com/@KRLegends-media" target="_blank"
                                    style="text-decoration: none; color: #ffc107;">KR Legends</a></span>
                        </p>
                    </div>
                    <div class="video-wrapper">
                        <div class="video-container">
                            <div class="video-loading-overlay">
                                <div class="spinner-border text-warning" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div class="video-glow"></div>
                            <iframe class="video-iframe"
                                src="https://www.youtube.com/embed/all--q7CSYw?si=A4rLgFmhj8u2kvIJ&autoplay=0"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--
        DIVIDER:
        Separador visual entre seções do conteúdo.
    -->
    <div class="section-divider"></div>

    <!--
        DISCORD SECTION:
        Seção para cativar utilizadores a entrar no servidor Discord do KR Legends
    -->
    <section class="discord-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12 text-center">
                    <h2 class="discord-title">Junta-te ao nosso Discord!</h2>
                    <p class="discord-subtitle">
                        Conecta-te com a comunidade KR Legends, participa em eventos exclusivos,
                        recebe atualizações em primeira mão e faz parte da nossa família de pilotos virtuais.
                    </p>
                </div>
            </div>

            <div class="discord-widget-container">
                <div class="discord-widget-card discord-float">
                    <div class="discord-widget-header">
                        <div class="discord-widget-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z" />
                            </svg>
                        </div>
                        <h3 class="discord-widget-title">KR Legends Discord</h3>
                        <p class="discord-widget-description">
                            Servidor do Discord oficial do KR Legends
                        </p>
                    </div>

                    <div class="discord-iframe-container">
                        <div class="discord-loading">
                            <div class="discord-loading-spinner"></div>
                        </div>
                        <iframe src="https://discord.com/widget?id=1385405982800216114&theme=dark"
                            class="discord-iframe" allowtransparency="true" frameborder="0"
                            sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts">
                        </iframe>
                    </div>
                </div>
            </div>

            <div class="discord-info-section">
                <div class="discord-features">
                    <div class="discord-feature">
                        <div class="discord-feature-icon">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h4 class="discord-feature-title">Chat em Tempo Real</h4>
                        <p class="discord-feature-text">
                            Conversa com outros pilotos, partilha estratégias e faz novos amigos
                        </p>
                    </div>

                    <div class="discord-feature">
                        <div class="discord-feature-icon">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <h4 class="discord-feature-title">Eventos Exclusivos</h4>
                        <p class="discord-feature-text">
                            Participa em torneios, competições e eventos especiais da comunidade
                        </p>
                    </div>

                    <div class="discord-feature">
                        <div class="discord-feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <h4 class="discord-feature-title">Atualizações Primeiro</h4>
                        <p class="discord-feature-text">
                            Recebe notificações sobre novas atualizações, patches e novidades
                        </p>
                    </div>

                    <div class="discord-feature">
                        <div class="discord-feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4 class="discord-feature-title">Comunidade Ativa</h4>
                        <p class="discord-feature-text">
                            Junta-te a uma comunidade vibrante de entusiastas de corridas
                        </p>
                    </div>
                </div>

                <a href="https://discord.gg/APKhkr97su" target="_blank" class="discord-cta-button">
                    <i class="bi bi-discord"></i>
                    Entrar no Discord
                </a>
            </div>
        </div>
    </section>

    <!-- Contêiner para o widget -->
    <div id="roblox-widget"></div>
    <!-- Script de inicialização -->
    <script src="js/Extras/kr_widget.js"></script>
    <script>
        initRobloxWidget(6662155690, { proxy: true });
    </script>

    <!--
        FAQ SECTION:
        Seção de Perguntas Frequentes com acordeão e sidebar para relatórios e feedback.
    -->
    <section class="faq-section py-5">
        <div class="container">
            <div class="row">
                <!-- Coluna principal de FAQ -->
                <div class="col-lg-8">
                    <h2 class="faq-title">Perguntas sobre o KR Legends</h2>
                    <div class="accordion" id="faqAccordion">

                    </div>
                </div>

                <!-- SIDEBAR: Relatório de Erro e Feedback -->
                <div class="col-lg-4">
                    <div class="sidebar">
                        <!-- Box para Feedback e Sugestões -->
                        <div class="sidebar-box">
                            <h4>Feedback & Sugestões</h4>
                            <p>Gostarias de ver algo novo no KR Legends? Conta-nos!</p>
                            <button onclick="openFeedbackModal()" class="btn btn-warning w-100">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                                Enviar Feedback
                            </button>
                        </div>

                        <!-- Box para relatar erros -->
                        <div class="sidebar-box">
                            <h4>Precisa de ajuda técnica?</h4>
                            <p>Encontre soluções para problemas de login, performance ou conta.</p>
                            <a href="Suporte.php"
                                class="btn btn-warning w-100 d-flex align-items-center justify-content-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" class="me-2">
                                    <path
                                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                    <line x1="12" y1="9" x2="12" y2="13" />
                                    <line x1="12" y1="17" x2="12.01" y2="17" />
                                </svg>
                                Ir para o Centro de Suporte
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--
        MODAIS PARA FEEDBACK:
        Contém os modais que serão abertos para enviar feedback, respectivamente.
    -->

    <!-- Feedback Modal -->
    <div id="kr-feedbackModal" class="feedback-modal-overlay">
        <div class="feedback-modal">
            <div class="feedback-modal-header">
                <h2>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg>
                    Feedback & Sugestões
                </h2>
                <button onclick="closeFeedbackModal()" class="feedback-close-btn">×</button>
            </div>

            <div id="kr-feedbackValidation" class="feedback-validation-errors hidden"></div>

            <form id="kr-feedbackForm" onsubmit="handleFeedbackSubmit(event)" class="feedback-modal-form">
                <div class="feedback-form-group">
                    <label>Tipo de Feedback *</label>
                    <select name="feedbackType" onchange="handleFeedbackTypeChange(this)">
                        <option value="">Selecione o tipo de feedback</option>
                        <option value="sugestao">Sugestão de Melhoria</option>
                        <option value="avaliacao">Avaliação do Jogo</option>
                        <option value="conteudo">Sugestão de Conteúdo</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>

                <div id="otherFeedbackType" class="feedback-form-group hidden">
                    <label>Especifique o tipo de feedback *</label>
                    <input type="text" name="otherFeedbackTypeText" placeholder="Digite o tipo de feedback específico">
                </div>

                <div class="feedback-form-group">
                    <label>Título da Sugestão *</label>
                    <input type="text" name="suggestionTitle" placeholder="Ex: Novo modo de corrida noturna">
                </div>

                <div class="feedback-form-group">
                    <label>Descrição Detalhada *</label>
                    <textarea name="suggestionDescription" rows="4"
                        placeholder="Descreva sua sugestão em detalhes..."></textarea>
                </div>

                <div class="feedback-form-group">
                    <label>Avaliação Geral *</label>
                    <div class="feedback-rating-group">
                        <button type="button" class="feedback-rating-btn" data-rating="1"
                            onclick="setRating(1)">1</button>
                        <button type="button" class="feedback-rating-btn" data-rating="2"
                            onclick="setRating(2)">2</button>
                        <button type="button" class="feedback-rating-btn" data-rating="3"
                            onclick="setRating(3)">3</button>
                        <button type="button" class="feedback-rating-btn" data-rating="4"
                            onclick="setRating(4)">4</button>
                        <button type="button" class="feedback-rating-btn" data-rating="5"
                            onclick="setRating(5)">5</button>
                    </div>
                </div>

                <div class="feedback-form-group">
                    <label>Como podemos contactá-lo?</label>
                    <textarea name="contactar" rows="4"
                        placeholder="Introduza o seu e-mail, telefone ou outro meio de contacto"></textarea>
                </div>

                <div class="feedback-form-group feedback-file-upload">
                    <div class="feedback-upload-group" id="uploadFeedback">
                        <label for="anexo" class="feedback-file-label" id="anexoLabel">
                            Anexar ficheiro <span class="feedback-file-hint">(imagens ou PDF)</span>
                        </label>
                        <button type="button" class="feedback-remove-file-btn hidden" id="removeAnexoBtn"
                            onclick="removeAnexo(event)">✖</button>
                    </div>
                    <input type="file" id="anexo" name="anexo" accept="image/*,application/pdf"
                        class="feedback-file-input" />
                </div>

                <button type="submit" class="feedback-submit-btn">Enviar Feedback</button>
            </form>
        </div>
    </div>

    <!--
        FOOTER:
        Rodapé do site com logo, mensagem de convite à comunidade, redes sociais e créditos.
    -->
    <footer class="bg-black py-5 border-top border-warning border-opacity-25">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-6">
                    <img src="Imagens/Logotipos/Logo KR Legends - soft trans recortado.png" alt="KR Legends" height="65"
                        class="mb-3">
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

    <!--
        Inclusão de scripts:
        Bootstrap, Script personalizado e traduções.
    -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/Extras/discord-widget.js"></script>
    <script src="js/Info_Comunidade.js"></script>
    <script src="js/Info_produtos.js"></script>
    <script src="js/Gerais/Geral.js"></script>
    <script src="js/Comunidade_Suporte/Accordion.js"></script>
    <script src="js/Gerais/cookieConsent.js"></script>
    <script src="js/Comunidade.js" defer></script>
    <script src="js/Extras/comunidade-modal-feedback.js"></script>
    <script src="js/Traducoes/geral.js"></script>
    <script src="js/Gerais/notification_API.js" defer></script>
</body>

</html>