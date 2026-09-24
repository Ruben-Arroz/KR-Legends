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

  <title>KR Legends - Policy</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="css/Politica.css" rel="stylesheet">
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
          <img src="Imagens/Logotipos/Logo KR Legends - soft trans recortado.png" alt="KR Legends" height="70">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">


          <ul class="navbar-nav me-auto">

            <!-- Sobre Dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="sobreDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
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
                  <a class="dropdown-item d-flex align-items-center" href="#">
                    <i class="fas fa-vote-yea me-2" aria-hidden="true"></i> Politica
                  </a>
                </li>
              </ul>
            </li>

            <!-- Comunidade & Social Dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="comSocialDropdown"
                role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16"
                      height="16" class="me-2" aria-hidden="true">
                      <title>Roblox</title>
                      <path
                        d="M5.164 0 .16 18.928 18.836 24 23.84 5.072Zm8.747 15.354-5.219-1.417 1.399-5.29 5.22 1.418-1.4 5.29z" />
                    </svg>
                    Roblox Group
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center" href="https://www.youtube.com/@KRLegends-media"
                    target="_blank">
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
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="jogoDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
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
      <h1 class="display-2 fw-bold text-warning mb-4 fade-in" data-translate="hero.welcome">A Nossa Política</h1>
      <p class="lead text-white mb-5 mx-auto slide-up" style="max-width: 600px;" data-translate="hero.subtitle">
        A sua privacidade é uma prioridade para nós. Saiba como utilizamos cookies para melhorar a sua experiência.
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

  <!-- Cookie Policy Content -->
  <main class="policy-container">
    <section class="policy-section intro-section fade-in">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-cookie-bite"></i>
        </div>
        <h2 class="section-title">Política de Cookies</h2>
      </div>
      <p class="section-description">
        Na KR Legends, respeitamos a sua privacidade e queremos ser transparentes sobre como utilizamos cookies e
        tecnologias semelhantes. Esta política explica o que são cookies, como os utilizamos e como pode controlar as
        suas preferências.
      </p>
    </section>

    <!-- What are Cookies Section -->
    <section class="policy-section fade-in">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-question-circle"></i>
        </div>
        <h2 class="section-title">O que são Cookies?</h2>
      </div>
      <div class="section-content">
        <p>
          Cookies são pequenos ficheiros de texto que são armazenados no seu dispositivo (computador, smartphone,
          tablet) quando visita um website. Estes ficheiros permitem reconhecer o utilizador e melhorar a sua
          experiência de navegação, garantindo funcionalidades essenciais, estatísticas e personalização de conteúdos.
        </p>
        <div class="info-card">
          <div class="info-card-icon">
            <i class="fas fa-info-circle"></i>
          </div>
          <div class="info-card-content">
            <h4>Sabia que?</h4>
            <p>O nome "cookie" vem da palavra "fortune cookie" (biscoito da sorte). Os primeiros cookies foram criados
              em 1994 por Lou Montulli, um programador da Netscape.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Why We Use Cookies Section -->
    <section class="policy-section fade-in">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <h2 class="section-title">Porque utilizamos Cookies?</h2>
      </div>
      <div class="section-content">
        <p>Os cookies ajudam-nos a:</p>
        <ul class="feature-list">
          <li class="feature-item">
            <i class="fas fa-shield-alt"></i>
            <span>Garantir o funcionamento seguro e eficiente do site</span>
          </li>
          <li class="feature-item">
            <i class="fas fa-sliders-h"></i>
            <span>Memorizar as suas preferências (idioma, sessão, consentimento)</span>
          </li>
          <li class="feature-item">
            <i class="fas fa-shopping-cart"></i>
            <span>Melhorar a experiência de compra</span>
          </li>
          <li class="feature-item">
            <i class="fas fa-chart-bar"></i>
            <span>Analisar o tráfego e desempenho da loja</span>
          </li>
          <li class="feature-item">
            <i class="fas fa-bullseye"></i>
            <span>Personalizar ofertas e comunicações de marketing, quando consentido</span>
          </li>
        </ul>
      </div>
    </section>

    <!-- Types of Cookies Section -->
    <section class="policy-section fade-in">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-layer-group"></i>
        </div>
        <h2 class="section-title">Tipos de Cookies que Utilizamos</h2>
      </div>
      <div class="section-content">
        <div class="accordion">
          <div class="accordion-item">
            <div class="accordion-header">
              <h3>Cookies Estritamente Necessários</h3>
              <span class="accordion-icon"><i class="fas fa-plus"></i></span>
            </div>
            <div class="accordion-content">
              <p>Essenciais para garantir o funcionamento básico da loja (login, gestão do carrinho, segurança). Estes
                não requerem consentimento.</p>
              <div class="cookie-example">
                <div class="cookie-example-header">Exemplos:</div>
                <ul>
                  <li><strong>session_id:</strong> Identificador de sessão necessário para manter o utilizador
                    autenticado</li>
                  <li><strong>csrf_token:</strong> Token de segurança para prevenir ataques CSRF</li>
                  <li><strong>cart_items:</strong> Armazena os itens no carrinho de compras</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <div class="accordion-header">
              <h3>Cookies de Desempenho e Estatística</h3>
              <span class="accordion-icon"><i class="fas fa-plus"></i></span>
            </div>
            <div class="accordion-content">
              <p>Permitem recolher dados anónimos sobre a utilização do site, ajudando-nos a melhorar a estrutura e o
                conteúdo (ex: Google Analytics).</p>
              <div class="cookie-example">
                <div class="cookie-example-header">Exemplos:</div>
                <ul>
                  <li><strong>_ga:</strong> Cookie do Google Analytics para distinguir utilizadores</li>
                  <li><strong>_gid:</strong> Cookie do Google Analytics para identificar utilizadores</li>
                  <li><strong>_gat:</strong> Cookie do Google Analytics para limitar a taxa de pedidos</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <div class="accordion-header">
              <h3>Cookies de Funcionalidade</h3>
              <span class="accordion-icon"><i class="fas fa-plus"></i></span>
            </div>
            <div class="accordion-content">
              <p>Guardam preferências como idioma, localização ou sessão de utilizador, proporcionando uma experiência
                personalizada.</p>
              <div class="cookie-example">
                <div class="cookie-example-header">Exemplos:</div>
                <ul>
                  <li><strong>user_language:</strong> Guarda a preferência de idioma do utilizador</li>
                  <li><strong>user_location:</strong> Guarda a localização do utilizador para conteúdo relevante</li>
                  <li><strong>theme_preference:</strong> Guarda a preferência de tema claro/escuro</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <div class="accordion-header">
              <h3>Cookies de Marketing e Publicidade</h3>
              <span class="accordion-icon"><i class="fas fa-plus"></i></span>
            </div>
            <div class="accordion-content">
              <p>Estes cookies são usados para fornecer publicidade relevante com base nos seus interesses, podendo
                ser definidos por parceiros externos (ex: Meta Ads, Google Ads).</p>
              <div class="cookie-example">
                <div class="cookie-example-header">Exemplos:</div>
                <ul>
                  <li><strong>_fbp:</strong> Cookie do Facebook para identificar navegadores para fins publicitários
                  </li>
                  <li><strong>_gcl_au:</strong> Cookie do Google AdSense para medir a eficácia da publicidade</li>
                  <li><strong>ads_prefs:</strong> Armazena preferências de publicidade do utilizador</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Third-Party Cookies Section -->
    <section class="policy-section fade-in">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-handshake"></i>
        </div>
        <h2 class="section-title">Cookies de Terceiros</h2>
      </div>
      <div class="section-content">
        <p>Podemos utilizar serviços de terceiros que também definem cookies (ex: OneSignal para notificações, Google
          Analytics para estatísticas, Facebook Pixel para remarketing). Estes terceiros são responsáveis pelas suas
          próprias políticas de privacidade.</p>

        <div class="partners-grid">
          <div class="partner-card">
            <img src="https://www.vixendigital.com/wp-content/uploads/2023/04/GA4_Logo_White.png" alt="Google Analytics"
              class="partner-logo">
            <h4>Google Analytics</h4>
            <p>Análise de tráfego e comportamento do utilizador</p>
            <a href="https://policies.google.com/privacy" target="_blank" class="partner-link">Política de Privacidade
              <i class="fas fa-external-link-alt"></i></a>
          </div>

          <div class="partner-card">
            <img src="https://pngimg.com/d/meta_PNG1.png" alt="Facebook Pixel" class="partner-logo">
            <h4>Meta Pixel</h4>
            <p>Rastreamento de conversão e remarketing</p>
            <a href="https://www.facebook.com/privacy/policy/" target="_blank" class="partner-link">Política de
              Privacidade <i class="fas fa-external-link-alt"></i></a>
          </div>

          <div class="partner-card">
            <img
              src="https://res.cloudinary.com/apideck/image/upload/w_196,f_auto/v1531305592/catalog/onesignal/icon128x128.png"
              alt="OneSignal" class="partner-logo">
            <h4>OneSignal</h4>
            <p>Notificações push para navegadores e dispositivos móveis</p>
            <a href="https://onesignal.com/privacy_policy" target="_blank" class="partner-link">Política de
              Privacidade <i class="fas fa-external-link-alt"></i></a>
          </div>
        </div>
      </div>
    </section>

    <!-- Cookie Management Section -->
    <section class="policy-section fade-in">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-cog"></i>
        </div>
        <h2 class="section-title">Gestão de Cookies</h2>
      </div>
      <div class="section-content">
        <p>Como pode controlar os cookies:</p>
        <div class="management-options">
          <div class="management-option">
            <div class="option-icon">
              <i class="fas fa-check-square"></i>
            </div>
            <div class="option-content">
              <h4>Banner de Consentimento</h4>
              <p>Através do banner de consentimento apresentado na primeira visita</p>
              <button class="btn cookie-settings-btn">Ajustar Preferências de Cookies</button>
            </div>
          </div>

          <div class="management-option">
            <div class="option-icon">
              <i class="fas fa-browser"></i>
            </div>
            <div class="option-content">
              <h4>Configurações do Navegador</h4>
              <p>A qualquer momento, alterando as configurações do navegador</p>
              <div class="browser-links">
                <a href="https://support.google.com/chrome/answer/95647" target="_blank" class="browser-link"><i
                    class="fab fa-chrome"></i> Chrome</a>
                <a href="https://support.mozilla.org/pt-PT/kb/cookies-informacao-que-websites-guardam-no-seu-computador"
                  target="_blank" class="browser-link"><i class="fab fa-firefox-browser"></i> Firefox</a>
                <a href="https://support.apple.com/pt-pt/guide/safari/sfri11471/mac" target="_blank"
                  class="browser-link"><i class="fab fa-safari"></i> Safari</a>
                <a href="https://support.microsoft.com/pt-pt/microsoft-edge/eliminar-cookies-no-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09"
                  target="_blank" class="browser-link"><i class="fab fa-edge"></i> Edge</a>
              </div>
            </div>
          </div>

          <div class="management-option">
            <div class="option-icon">
              <i class="fas fa-tools"></i>
            </div>
            <div class="option-content">
              <h4>Ferramentas Específicas</h4>
              <p>Ou através de ferramentas específicas, como:</p>
              <div class="tool-links">
                <a href="https://www.youronlinechoices.com/pt/" target="_blank" class="tool-link">YourOnlineChoices</a>
                <a href="https://optout.networkadvertising.org/" target="_blank" class="tool-link">Network Advertising
                  Initiative</a>
              </div>
            </div>
          </div>
        </div>

        <div class="warning-notice">
          <i class="fas fa-exclamation-triangle"></i>
          <p>A desativação de cookies essenciais poderá comprometer funcionalidades críticas da loja.</p>
        </div>
      </div>
    </section>

    <!-- Implementation and Compliance Section -->
    <section class="policy-section fade-in">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-tasks"></i>
        </div>
        <h2 class="section-title">Implementação e Conformidade Interna</h2>
      </div>
      <div class="section-content">
        <p>Nesta secção, explicamos como aplicamos os princípios desta política no dia a dia da nossa organização:</p>

        <div class="accordion">
          <div class="accordion-item">
            <div class="accordion-header">
              <h3>Gestão de Consentimento</h3>
              <span class="accordion-icon"><i class="fas fa-plus"></i></span>
            </div>
            <div class="accordion-content">
              <p>O nosso sistema de gestão de consentimento baseia-se nos seguintes princípios:</p>
              <ul class="feature-list">
                <li class="feature-item">
                  <i class="fas fa-bell"></i>
                  <span style="color: white;">O banner de cookies é disparado no primeiro acesso e nunca volta a surgir
                    se o utilizador já tiver aceite ou recusado categorias.</span>
                </li>
                <li class="feature-item">
                  <i class="fas fa-database"></i>
                  <span style="color: white;">As escolhas são gravadas numa base de dados segura, associadas à sua conta
                    (se estiver autenticado) ou ao identificador do navegador.</span>
                </li>
              </ul>
              <div class="info-card">
                <div class="info-card-icon">
                  <i class="fas fa-shield-alt"></i>
                </div>
                <div class="info-card-content">
                  <h4>Segurança do Consentimento</h4>
                  <p>O registo de consentimento é armazenado de forma segura e inclui data, hora e preferências
                    específicas para garantir a conformidade com as leis de privacidade.</p>
                </div>
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <div class="accordion-header">
              <h3>Fluxo de Atualização Automática</h3>
              <span class="accordion-icon"><i class="fas fa-plus"></i></span>
            </div>
            <div class="accordion-content">
              <p>Para manter a transparência e conformidade, implementamos um sistema inteligente que:</p>
              <ul class="feature-list">
                <li class="feature-item">
                  <i class="fas fa-sync-alt"></i>
                  <span style="color: white;">Sempre que alteramos categorias de cookies ou adicionamos novos
                    fornecedores, o banner é automaticamente atualizado para recolher novo consentimento.</span>
                </li>
                <li class="feature-item">
                  <i class="fas fa-code"></i>
                  <span style="color: white;">Este processo é totalmente automatizado, sem necessidade de intervenção
                    manual por parte da equipa técnica.</span>
                </li>
              </ul>
              <div class="cookie-example">
                <div class="cookie-example-header">Como funciona:</div>
                <ol style="color: white;" id="li_number">
                  <li> O sistema deteta alterações na configuração de cookies;</li>
                  <li>Verifica a versão do consentimento armazenado para cada utilizador;</li>
                  <li>Se houver discrepância, solicita novo consentimento na próxima visita;</li>
                </ol>
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <div class="accordion-header">
              <h3>Monitorização e Relatórios</h3>
              <span class="accordion-icon"><i class="fas fa-plus"></i></span>
            </div>
            <div class="accordion-content">
              <p>Para melhorar continuamente a nossa abordagem de consentimento:</p>
              <ul class="feature-list">
                <li class="feature-item">
                  <i class="fas fa-chart-pie"></i>
                  <span style="color: white;">Geramos relatórios trimestrais sobre taxas de aceite/recusa de cookies
                    para aferir a eficácia das mensagens de consentimento.</span>
                </li>
                <li class="feature-item">
                  <i class="fas fa-pencil-alt"></i>
                  <span style="color: white;">Ajustamos a linguagem ou posicionamento dos avisos com base nos dados
                    recolhidos, se necessário.</span>
                </li>
              </ul>
              <div class="info-card">
                <div class="info-card-icon">
                  <i class="fas fa-lightbulb"></i>
                </div>
                <div class="info-card-content">
                  <h4>Melhoria Contínua</h4>
                  <p>Os nossos relatórios analisam as taxas de consentimento por tipo de cookie, permitindo-nos entender
                    melhor as preferências dos utilizadores e ajustar a nossa estratégia para equilibrar experiência e
                    privacidade.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="policy-section fade-in">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-bell"></i> <!-- Ícone de notificação -->
        </div>
        <h2 class="section-title">Política de Notificações</h2>
      </div>
      <p class="section-description">
        Na KR Legends, utilizamos notificações para mantê-lo informado sobre novidades, atualizações de jogos e outras
        informações importantes.
        Essa política explica como você pode gerenciar as notificações que recebe.
      </p>

      <!-- Cartões de Informação -->
      <div class="section-content">
        <div class="info-card">
          <div class="info-card-icon">
            <i class="fas fa-question-circle"></i>
          </div>
          <div class="info-card-content">
            <h4>Como Funciona?</h4>
            <p>Ao visitar nosso site, o navegador solicitará sua permissão para enviar notificações. Aceitando, você
              receberá atualizações importantes. Se recusar, não receberá notificações. Pode consultar a <a
                href="Suporte.php" class="link-suporte">página de Suporte</a> para verificar se a permissão foi aceite,
              recusada ou se ainda se encontra em default.</p>
          </div>
        </div>

        <div class="info-card">
          <div class="info-card-icon">
            <i class="fas fa-cogs"></i>
          </div>
          <div class="info-card-content">
            <h4>Gerenciamento de Notificações</h4>
            <p>Você pode ajustar as preferências de notificações a qualquer momento nas configurações do seu navegador.
            </p>
          </div>
        </div>

        <div class="info-card">
          <div class="info-card-icon">
            <i class="fas fa-info-circle"></i>
          </div>
          <div class="info-card-content">
            <h4>Sabia que?</h4>
            <p>As notificações podem ser configuradas para enviar apenas o que é mais relevante para você.</p>
          </div>
        </div>
      </div>
    </section>


    <!-- Policy Changes Section -->
    <section class="policy-section fade-in">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-history"></i>
        </div>
        <h2 class="section-title">Alterações à Política</h2>
      </div>
      <div class="section-content">
        <p>A KR Legends reserva-se o direito de atualizar esta política a qualquer momento, por motivos legais,
          técnicos ou operacionais. Qualquer alteração significativa será comunicada via banner ou notificação.</p>

        <div class="update-info">
          <div class="update-date">
            <i class="fas fa-calendar-check"></i>
            <span>Última atualização: <strong>23 de abril de 2025</strong></span>
          </div>
          <div class="version-history">
            <button class="btn version-history-btn">
              <i class="fas fa-clock-rotate-left"></i>
              Ver Histórico de Versões
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="policy-section contact-section fade-in">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-envelope"></i>
        </div>
        <h2 class="section-title">Contactos</h2>
      </div>
      <div class="section-content">
        <p>Para mais informações sobre a nossa Política de Cookies ou tratamento de dados pessoais, entre em contacto:
        </p>

        <div class="contact-info">
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-envelope"></i>
            </div>
            <div class="contact-detail">
              <h4>Email</h4>
              <a href="mailto:suporte@krlegends.pt">kr.legends.suport@gmail.com</a>
            </div>
          </div>

          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="contact-detail">
              <h4>Endereço</h4>
              <address>Rua S.Ana, 105<br>Avanca, Portugal</address>
            </div>
          </div>
        </div>

        <div class="contact-form-container">
          <h3 class="contact-form-title">Envie-nos uma Mensagem</h3>
          <form class="contact-form">
            <div class="form-group">
              <label for="name">Nome</label>
              <input type="text" id="name" name="name" placeholder="Seu nome" required>
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" placeholder="Seu email" required>
            </div>
            <div class="form-group">
              <label for="subject">Assunto</label>
              <select id="subject" name="subject" required>
                <option value="">Selecione o assunto</option>
                <option value="privacy">Política de Privacidade</option>
                <option value="cookies">Cookies</option>
                <option value="data">Meus Dados</option>
                <option value="other">Outro</option>
              </select>
            </div>
            <div class="form-group">
              <label for="message">Mensagem</label>
              <textarea id="message" name="message" placeholder="Sua mensagem" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn submit-btn">Enviar Mensagem</button>
          </form>
        </div>
      </div>
    </section>
  </main>

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

  <!-- Primeiro carregue o Bootstrap para que ele esteja pronto para os outros scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Scripts gerais que podem ser usados em todo o site -->
  <script src="js/Gerais/Geral.js"></script>
  <script src="js/Gerais/cookieConsent.js"></script>

  <!-- Scripts específicos da página -->
  <script src="js/Info_produtos.js"></script>
  <script src="js/Politica.js"></script>

  <!-- Por último, carregue o notification_API.js com defer para garantir que ele seja executado por último -->
  <script src="js/Gerais/notification_API.js" defer></script>

</body>

</html>