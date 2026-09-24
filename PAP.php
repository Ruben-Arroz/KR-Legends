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

    <title>KR Legends - PAP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="css/PAP.css" rel="stylesheet">
    <link href="css/Geral.css" rel="stylesheet">
    <link href="css/cookieConsent.css" rel="stylesheet">
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
                                    <a class="dropdown-item d-flex align-items-center" href="#">
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
    <!-- Hero Section -->
    <section id="hero" class="min-vh-100 d-flex align-items-center text-center position-relative overflow-hidden">
        <div class="hero-overlay"></div>
        <div class="container position-relative hero-content">
            <h1 class="display-2 fw-bold text-warning mb-4 fade-in" data-translate="hero.welcome">O Projeto PAP</h1>
            <p class="lead text-white mb-5 mx-auto slide-up" style="max-width: 600px;" data-translate="hero.subtitle">
                Veja como foi o nosso caminho ao longo do desenvolvimento do nosso projeto enquanto Prova de Aptidão
                Profissional.
            </p>
            <a href="https://www.roblox.com/pt/games/113586179382037/KR-Legends-Beta" target="_blank"
                class="btn btn-warning btn-lg px-5 py-3 fw-bold bounce">
                <i class="bi bi-controller me-2"></i> <span data-translate="hero.playNow">Jogar Agora</span>
            </a>
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

    <!-- This content is to be inserted after the hero section in the HTML file -->
    <div class="pap-gradient-wrapper">

        <!-- Project Summary Section -->
        <section id="project-summary" class="pap-section">
            <div class="container">
                <h2 class="section-title text-center mb-5">Resumo do Projeto</h2>
                <div class="row">
                    <!-- Objectives -->
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="summary-block">
                            <div class="summary-icon">
                                <i class="bi bi-bullseye"></i>
                            </div>
                            <h3 class="section-subtitle">Objetivos</h3>
                            <p>Criar todo o ecossistema KR Legends como PAP. Desenvolver um jogo de corrida em Roblox
                                com pistas temáticas e sistema JDM de personalização, projetar e implementar um website
                                responsivo com loja online e conta de utilizador, conceber mockups de merch e
                                estabelecer presença ativa em redes sociais para divulgação e suporte.</p>
                        </div>
                    </div>

                    <!-- Methodology -->
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="summary-block">
                            <div class="summary-icon">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                            <h3 class="section-subtitle">Metodologia</h3>
                            <p>As tarefas foram divididas entre Kauã, responsável pelas redes sociais e Rúben,
                                responsável pelo website; o design de merch e o game design partilharam-se em tarefas de
                                esforço equilibrado. Utilizaram VS Code, Roblox Studio, Canva e CapCut, numa sequência
                                colaborativa de integração de conteúdo contínua, acompanhada por testes regulares de
                                aparência, funcionalidades e desempenho em cada componente.</p>
                        </div>
                    </div>

                    <!-- Expected Results -->
                    <div class="col-md-4">
                        <div class="summary-block">
                            <div class="summary-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <h3 class="section-subtitle">Resultados Esperados</h3>
                            <p>Prevemos entregar um jogo leve e fluido com mapa amplo, sistema de dinheiro fictício,
                                loja de carros, oficina de personalização e alcançado centenas de jogadores; um website
                                com criação de conta e rich content; pelo menos 20 peças de merch mockup; e presença em
                                várias plataformas sociais, publicando conteúdos de qualidade e alcançando algumas
                                centenas de interações.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tools Used Section -->
        <section id="tools" class="pap-section">
            <div class="container">
                <h2 class="section-title text-center mb-5">Ferramentas Usadas</h2>
                <div class="row">
                    <!-- Roblox Studio -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="tool-card text-center">
                            <div class="tool-icon">
                                <i class="fas fa-cube"></i>
                            </div>
                            <h4 class="tool-title">Roblox Studio</h4>
                            <p class="tool-description">Criação de mapas e scripts em Lua para o desenvolvimento do
                                jogo.
                            </p>
                        </div>
                    </div>

                    <!-- VS Code -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="tool-card text-center">
                            <div class="tool-icon">
                                <i class="fas fa-code"></i>
                            </div>
                            <h4 class="tool-title">VS Code</h4>
                            <p class="tool-description">Edição de código e integração Git para desenvolvimento
                                colaborativo.
                            </p>
                        </div>
                    </div>

                    <!-- My SQL -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="tool-card text-center">
                            <div class="tool-icon">
                                <i class="bi bi-filetype-sql"></i>
                            </div>
                            <h4 class="tool-title">My SQL</h4>
                            <p class="tool-description">Armazenamento e manutenção de conteúdo para o website
                            </p>
                        </div>
                    </div>

                    <!-- Redes Sociais -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="tool-card text-center">
                            <div class="tool-icon">
                                <i class="bi bi-phone"></i>
                            </div>
                            <h4 class="tool-title">Redes sociais</h4>
                            <p class="tool-description">Publicidade do jogo e de merch e fornecimento de informações
                                sobre o projeto
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Attachments Section -->
        <section id="attachments" class="pap-section">
            <div class="container">
                <h2 class="section-title text-center mb-5">Anexos</h2>

                <!-- Acordeão para dispositivos móveis -->
                <div class="custom-accordion d-block d-md-none" id="mobile-accordion">
                    <!-- O conteúdo será preenchido dinamicamente por JS -->
                </div>

                <!-- Card com abas para desktop -->
                <div class="attachments-card d-none d-md-block" id="desktop-tabs">
                    <div class="tab-titles">
                        <button class="tab-link active" data-tab="presentations">Apresentações</button>
                        <button class="tab-link" data-tab="reports">Relatórios</button>
                        <button class="tab-link" data-tab="others">Outros</button>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane active" id="presentations">
                            <!-- O conteúdo será preenchido dinamicamente por JS -->
                        </div>
                        <div class="tab-pane" id="reports">
                            <!-- O conteúdo será preenchido dinamicamente por JS -->
                        </div>
                        <div class="tab-pane" id="others">
                            <!-- O conteúdo será preenchido dinamicamente por JS -->
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Timeline Section -->
        <section id="timeline" class="pap-section">
            <div class="container">
                <h2 class="section-title text-center mb-5">Cronograma de Fases</h2>
                <div class="timeline-container">
                    <div class="timeline-track">
                        <div class="timeline-markers">
                            <div class="timeline-marker" data-phase="planeamento">
                                <div class="timeline-label">Planeamento</div>
                            </div>
                            <div class="timeline-marker" data-phase="design">
                                <div class="timeline-label">Design</div>
                            </div>
                            <div class="timeline-marker" data-phase="prototipacao">
                                <div class="timeline-label">Prototipação</div>
                            </div>
                            <div class="timeline-marker" data-phase="testes">
                                <div class="timeline-label">Testes</div>
                            </div>
                            <div class="timeline-marker" data-phase="polimento">
                                <div class="timeline-label">Polimento</div>
                            </div>
                        </div>
                    </div>
                    <div class="timeline-content">
                        <!-- Content will be dynamically loaded by JavaScript -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Obstacles Section -->
        <section id="obstacles" class="pap-section">
            <div class="container">
                <h2 class="section-title text-center mb-5">Principais Obstáculos</h2>
                <div class="row obstacles-container">
                    <!-- Obstacle 1 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="obstacle-card">
                            <div class="obstacle-card-front">
                                <div class="obstacle-icon">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <h4 class="obstacle-title">Banimento da conta Instagram</h4>
                                <p>Clique para ver como resolvemos este desafio</p>
                                <div class="obstacle-hint">
                                    <span>Ver solução</span>
                                    <i class="bi bi-arrow-repeat"></i>
                                </div>
                            </div>
                            <div class="obstacle-card-back">
                                <h4 class="obstacle-title">Banimento da conta Instagram</h4>
                                <div class="obstacle-label">Impacto:</div>
                                <p class="obstacle-text mb-2">Perda de canal oficial de divulgação e comunicação com a
                                    comunidade e de seguidores</p>
                                <div class="obstacle-label">Solução:</div>
                                <p class="obstacle-text mb-2">Criámos uma nova conta noutro dispositivo e migrámos parte
                                    do conteúdo para o Discord e TikTok, tentando recuperar o alcance perdido.</p>
                                <div class="obstacle-hint">
                                    <span>Voltar</span>
                                    <i class="bi bi-arrow-repeat"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Obstacle 2 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="obstacle-card">
                            <div class="obstacle-card-front">
                                <div class="obstacle-icon">
                                    <i class="bi bi-search"></i>
                                </div>
                                <h4 class="obstacle-title">Aprendizagem e aplicação</h4>
                                <p>Clique para ver como resolvemos este desafio</p>
                                <div class="obstacle-hint">
                                    <span>Ver solução</span>
                                    <i class="bi bi-arrow-repeat"></i>
                                </div>
                            </div>
                            <div class="obstacle-card-back">
                                <h4 class="obstacle-title">Aprendizagem e aplicação</h4>
                                <div class="obstacle-label">Impacto:</div>
                                <p class="obstacle-text mb-2">Ao longo da nossa PAP tivemos que adquirir conhecimentos
                                    novos de forma autônoma para que possamos em simultâneo aplicar no nosso projeto</p>
                                <div class="obstacle-label">Solução:</div>
                                <p class="obstacle-text mb-2">Através de comunidades e várias pesquisas fomos aumentando
                                    a nossa base de conhecimento necessário para o desenvolvimento do projeto</p>
                                <div class="obstacle-hint">
                                    <span>Voltar</span>
                                    <i class="bi bi-arrow-repeat"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Obstacle 3 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="obstacle-card">
                            <div class="obstacle-card-front">
                                <div class="obstacle-icon">
                                    <i class="bi bi-hourglass"></i>
                                </div>
                                <h4 class="obstacle-title">Gestão de tempo</h4>
                                <p>Clique para ver como resolvemos este desafio</p>
                                <div class="obstacle-hint">
                                    <span>Ver solução</span>
                                    <i class="bi bi-arrow-repeat"></i>
                                </div>
                            </div>
                            <div class="obstacle-card-back">
                                <h4 class="obstacle-title">Gestão de tempo</h4>
                                <div class="obstacle-label">Impacto:</div>
                                <p class="obstacle-text mb-2">Dificuldade em conciliar tarefas escolares, vida pessoal e
                                    desenvolvimento do projeto</p>
                                <div class="obstacle-label">Solução:</div>
                                <p class="obstacle-text mb-2">Criámos um cronograma rigoroso, com blocos dedicados e uso
                                    de ferramentas de time-tracking</p>
                                <div class="obstacle-hint">
                                    <span>Voltar</span>
                                    <i class="bi bi-arrow-repeat"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Conclusion Section -->
        <section id="conclusion" class="pap-section">
            <div class="container">
                <h2 class="section-title mb-4">Conclusão & Reflexão</h2>
                <p class="mb-5">
                    Concluímos que a nossa jornada na Prova de Aptidão Profissional reforça a nossa visão de futuro,
                    onde a paixão pela inovação e a colaboração se fundem para criar experiências únicas. Acreditamos
                    que cada desafio nos impulsiona a repensar limites, valorizando a qualidade e a identidade que
                    definem o nosso projeto. Encorajamos a comunidade a crescer connosco, partilhando ideias e
                    inspirando novos rumos. Mantemo-nos firmes no compromisso de elevar o KR Legends a patamares cada
                    vez mais ambiciosos, sustentados por criatividade, resiliência e trabalho em equipa.
                </p>

                <h3 class="section-subtitle">Próximos Passos</h3>
                <ul class="next-steps-list">
                    <li>Otimização para dispositivos móveis com controles adaptados e interfaces responsivas</li>
                    <li>Implementação de sistema de classificação avançado com temporadas e recompensas exclusivas</li>
                    <li>Expansão do conteúdo com novas pistas, veículos e modos de jogo</li>
                    <li>Aprimoramento do sistema de personalização de veículos com mais opções visuais e de desempenho
                    </li>
                    <li>Integração com plataformas de streaming para compartilhamento de conteúdo e torneios ao vivo
                    </li>
                </ul>
            </div>
        </section>
    </div>

    <!-- Footer -->
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
                <p class="text-white-50 mb-2" data-translate="footer.rights">©2026 KR Legends. Todos os direitos
                    reservados.</p>
                <p class="text-white-50 small" data-translate="footer.developed">
                    Desenvolvido pela equipa de KR Legends com paixão pelo automobilismo e programação.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/PAP.js"></script>
    <script src="js/Info_produtos.js"></script>
    <script src="js/Gerais/Geral.js"></script>
    <script src="js/Gerais/cookieConsent.js"></script>
    <script src="js/Gerais/notification_API.js" defer></script>
</body>

</html>