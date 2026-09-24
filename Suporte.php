<?php
// ----------------------
// Sessão e BD
// ----------------------
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();

require_once __DIR__ . '/../databaseconnect.php'; // caminho ajustado

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Receção e sanitização
    $user_id = $_SESSION['id'] ?? null;
    $ctx_raw = $_POST['location'] ?? '';
    $type = filter_input(INPUT_POST, 'errorType', FILTER_SANITIZE_SPECIAL_CHARS);
    $otherType = filter_input(INPUT_POST, 'otherErrorTypeText', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
    $title = filter_input(INPUT_POST, 'problemTitle', FILTER_SANITIZE_SPECIAL_CHARS);
    $desc = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
    $steps = filter_input(INPUT_POST, 'steps', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
    $freq = filter_input(INPUT_POST, 'frequency', FILTER_SANITIZE_SPECIAL_CHARS);
    $contactMethod = filter_input(INPUT_POST, 'contactMethod', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

    // 2) Validação de contexto
    $allowedCtx = ['website', 'game'];
    $ctx = in_array($ctx_raw, $allowedCtx, true) ? $ctx_raw : null;

    // 3) Verificação de sessão - exceto para problemas de login/registo
    if ($type !== 'login_registo' && empty($user_id)) {
        $_SESSION['report_errors'] = ['Deve ter sessão iniciada para submeter um pedido de suporte.'];
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // 4) Validação server-side
    $errs = [];
    if (!$ctx)
        $errs[] = 'Contexto do erro inválido.';
    if (!$type)
        $errs[] = 'Tipo de erro é obrigatório.';
    if ($type === 'outro' && !$otherType)
        $errs[] = 'Descreva o tipo de erro.';
    if (!$title)
        $errs[] = 'Título é obrigatório.';
    if (!$desc)
        $errs[] = 'Descrição é obrigatória.';
    if (!$freq)
        $errs[] = 'Frequência é obrigatória.';
    if ($type === 'login_registo' && !$contactMethod)
        $errs[] = 'Forma de contacto é obrigatória para problemas de login/registo.';

    if ($errs) {
        $_SESSION['report_errors'] = $errs;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // 5) Inserção inicial
    $stmt = $mysqli->prepare("
      INSERT INTO KR_SUPPORT_REQUESTS
        (USER_ID, ERROR_CONTEXT, ERROR_TYPE, OTHER_ERROR_TYPE, TITLE, DESCRIPTION, REPRO_STEPS, FREQUENCY, contact_method)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'issssssss',
        $user_id,
        $ctx,
        $type,
        $otherType,
        $title,
        $desc,
        $steps,
        $freq,
        $contactMethod
    );
    $stmt->execute();
    $reportId = $mysqli->insert_id;
    $stmt->close();

    // 6) Tratamento de ficheiro
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp4'];
    $maxSize = 25 * 1024 * 1024; // 25 MB

    if (
        isset($_FILES['anexo'])
        && $_FILES['anexo']['error'] === UPLOAD_ERR_OK
        && is_uploaded_file($_FILES['anexo']['tmp_name'])
    ) {
        $tmp = $_FILES['anexo']['tmp_name'];
        $size = $_FILES['anexo']['size'];
        $orig = $_FILES['anexo']['name'];
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

        if ($size <= $maxSize && in_array($ext, $allowedExt, true)) {
            $destDir = __DIR__ . '/Imagens/reports/';
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $newName = "report_{$reportId}.{$ext}";
            $dest = $destDir . $newName;

            if (move_uploaded_file($tmp, $dest)) {
                $pathWeb = "Imagens/reports/{$newName}";
                $upd = $mysqli->prepare("
                UPDATE KR_SUPPORT_REQUESTS
                   SET FILE_PATH = ?
                 WHERE ID_REPORT = ?
            ");
                $upd->bind_param('si', $pathWeb, $reportId);
                $upd->execute();
                $upd->close();
            } else {
                error_log("Falha ao mover uploaded_file para {$dest}");
            }
        } else {
            error_log("Ficheiro inválido: tamanho={$size} ext={$ext}");
        }
    }

    $_SESSION['report_success'] = 'Pedido de suporte enviado com sucesso!';
    header('Location: Suporte.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <!--
        Cabeçalho: Define a codificação, configura a viewport, título da página,
        e inclui os links para as folhas de estilo (Bootstrap, Bootstrap Icons e CSS personalizado).
    -->
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

    <title>KR Legends - Suporte</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/Suporte.css" rel="stylesheet">
    <link href="css/Geral.css" rel="stylesheet">
    <link href="css/Extras/active_notification.css" rel="stylesheet">
    <link href="css/cookieConsent.css" rel="stylesheet">
    <link href="css/includes/profileMenu.css" rel="stylesheet">
    <link href="css/includes/modal-logout.css" rel="stylesheet">
    <link href="css/includes/modal-suporte-report.css" rel="stylesheet">
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
                Estamos Aqui para Ajudar
            </h1>
            <!-- Subtítulo com efeito de slide -->
            <p class="lead text-white mb-5 mx-auto slide-up" style="max-width: 600px;" data-translate="hero.subtitle">
                Precisa de ajuda? Estamos aqui para garantir que a sua experiência no <b>KR Legends</b> seja a melhor
                possível. Veja abaixo as opções de suporte.
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

    <!--Game Status-->
    <section id="game-status" class="py-5 bg-black text-light">
        <div class="container">
            <h2 class="section-title text-center mb-4">Estado do Jogo</h2>

            <div class="ping-container mb-4 p-3 rounded">
                <span><strong>Latência do Backend:</strong>
                    <span id="ping-backend">—</span>
                    <i class="bi bi-info-circle subtle-icon" data-bs-toggle="tooltip"
                        title="Tempo de resposta do servidor de backend."></i>
                </span>
            </div>

            <div id="place-status-container" class="row justify-content-center g-4">
                <!-- Cards injetados pelo JS -->
            </div>

            <!-- Ações -->
            <div class="status-details d-flex flex-wrap justify-content-center gap-3 mt-5">
                <a href="Atualizacoes.php" class="btn btn-outline-warning px-4">Ver atualizações</a>
                <a id="btn-notificacoes" href="#" class="btn btn-outline-secondary px-4">Ativar notificações</a>
            </div>
        </div>
    </section>

    <!--
        DIVIDER:
        Separador visual entre seções do conteúdo.
    -->
    <div class="section-divider"></div>

    <!--
        FAQ SECTION:
        Seção de Perguntas Frequentes com acordeão e sidebar para relatórios e feedback.
    -->
    <section class="faq-section py-5">
        <div class="container">
            <div class="row">
                <!-- Coluna principal de FAQ -->
                <div class="col-lg-8">
                    <h2 class="faq-title">Centro de Suporte KR Legends</h2>
                    <div class="accordion" id="faqAccordion">

                    </div>
                </div>

                <!-- SIDEBAR: Relatório de Erro e Feedback -->
                <div class="col-lg-4">
                    <div class="sidebar">
                        <!-- Box para relatar erros -->
                        <div class="sidebar-box">
                            <h4>Relatar Erro</h4>
                            <p>Encontraste algum bug ou problema? Ajuda-nos a melhorar reportando-o.</p>
                            <button onclick="openReportModal()" class="btn btn-warning w-100">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path
                                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                    <line x1="12" y1="9" x2="12" y2="13" />
                                    <line x1="12" y1="17" x2="12.01" y2="17" />
                                </svg>
                                Relatar Erro
                            </button>
                        </div>
                        <!-- Box para Feedback e Sugestões -->
                        <div class="sidebar-box">
                            <h4>Tem sugestões?</h4>
                            <p>Gostarias de ver algo novo no KR Legends? Conta-nos!</p>
                            <a href="Comunidade.php"
                                class="btn btn-warning w-100 d-flex align-items-center justify-content-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" style="margin-right: 0.19rem;">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                                Enviar Sugestão
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Error Report Modal -->
    <div id="kr-errorModal" class="report-modal-overlay">
        <div class="report-modal">
            <div class="report-modal-header">
                <h2>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    Relatar Erro
                </h2>
                <button onclick="closeErrorModal()" class="report-close-btn" aria-label="Fechar modal">×</button>
            </div>

            <?php if (isset($_SESSION['report_errors'])): ?>
                <div class="report-validation-errors">
                    <?php foreach ($_SESSION['report_errors'] as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['report_errors']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['report_success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['report_success']) ?>
                </div>
                <?php unset($_SESSION['report_success']); ?>
            <?php endif; ?>

            <div id="kr-errorValidation" class="report-validation-errors hidden"></div>

            <form id="kr-errorForm" onsubmit="handleErrorSubmit(event)" class="report-modal-form"
                action="<?= htmlentities($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="location" id="locationInput" value="website">

                <div class="report-form-group">
                    <label>Onde se encontra o erro? *</label>
                    <div class="report-button-group">
                        <button type="button" class="report-location-btn active" data-location="website"
                            onclick="setErrorLocation('website')">Website</button>
                        <button type="button" class="report-location-btn" data-location="game"
                            onclick="setErrorLocation('game')">Jogo do Roblox</button>
                    </div>
                </div>

                <div class="report-form-group">
                    <label for="errorTypeSelect">Tipo de Erro *</label>
                    <select name="errorType" id="errorTypeSelect" onchange="handleErrorTypeChange(this)">
                        <option value="">Selecione o tipo de erro</option>
                    </select>
                </div>

                <div id="otherErrorType" class="report-form-group hidden">
                    <label for="otherErrorTypeText">Especifique o tipo de erro *</label>
                    <input type="text" name="otherErrorTypeText" id="otherErrorTypeText"
                        placeholder="Digite o tipo de erro específico">
                </div>

                <div id="contactMethodField" class="contact-method-field hidden">
                    <label for="contactMethod">Forma de contactar *</label>
                    <input type="text" name="contactMethod" id="contactMethod"
                        placeholder="Ex: email@exemplo.com, +351 123 456 789, Discord: utilizador#1234">
                    <small
                        style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem; display: block;">
                        Forneça uma forma de contacto para que possamos ajudar com o seu problema de login/registo.
                    </small>
                </div>

                <div class="report-website-fields">
                    <div class="report-form-group">
                        <label for="problemTitle">Título do Problema *</label>
                        <input type="text" name="problemTitle" id="problemTitle"
                            placeholder="Ex: Menu não abre corretamente">
                    </div>

                    <div class="report-form-group">
                        <label for="description">Descrição Detalhada *</label>
                        <textarea name="description" id="description" rows="4"
                            placeholder="Descreva o problema em detalhes..."></textarea>
                    </div>

                    <div class="report-form-group">
                        <label for="steps">Como Reproduzir o Erro</label>
                        <textarea name="steps" id="steps" rows="3"
                            placeholder="Passos para reproduzir o erro..."></textarea>
                    </div>

                    <div class="report-form-group">
                        <label for="frequency">Frequência do Erro *</label>
                        <select name="frequency" id="frequency">
                            <option value="">Selecione a frequência</option>
                            <option value="sempre">Sempre</option>
                            <option value="frequente">Frequentemente</option>
                            <option value="as_vezes">Às vezes</option>
                            <option value="raramente">Raramente</option>
                        </select>
                    </div>

                    <div class="report-form-group report-file-upload">
                        <label for="anexo" class="report-file-label" id="anexoLabel">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                            </svg>
                            Anexar ficheiro <span class="report-file-hint">(imagens, PDF ou vídeo)</span>
                        </label>
                        <input type="file" id="anexo" name="anexo" accept="image/*,application/pdf,video/mp4"
                            class="report-file-input" />
                    </div>
                </div>

                <button type="submit" class="report-submit-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        style="margin-right: 0.5rem;">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22,2 15,22 11,13 2,9 22,2"></polygon>
                    </svg>
                    Enviar Relatório
                </button>
            </form>
        </div>
    </div>

    <!-- Login Prompt Modal -->
    <div id="kr-loginPromptModal" class="login-prompt-modal-overlay hidden">
        <div class="login-prompt-modal">
            <div class="login-prompt-modal-header">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <circle cx="12" cy="16" r="1"></circle>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Acesso Necessário
                </h3>
                <button onclick="closeLoginPromptModal()" class="report-close-btn" aria-label="Fechar modal">×</button>
            </div>
            <div class="login-prompt-modal-body">
                <p>Para submeter um pedido de suporte, precisa de ter sessão iniciada na sua conta KR Legends.</p>
            </div>
            <div class="login-prompt-modal-footer">
                <button type="button" class="login-prompt-cancel-btn"
                    onclick="closeLoginPromptModal()">Cancelar</button>
                <a href="https://alpha.soaresbasto.pt/~a29621/KRLegends/Login-Cadastro/Login.php"
                    class="login-prompt-login-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        style="margin-right: 0.5rem;">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10,17 15,12 10,7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    Iniciar Sessão
                </a>
            </div>
        </div>
    </div>

    <!-- Modal de toast: Erro e Tutorial -->
    <div id="kr-modal" class="kr-modal-overlay hidden">
        <div class="kr-modal">
            <div class="modal-header">
                <h2 id="kr-modal-title">
                    🔔 <span id="kr-modal-heading">Ativar Notificações</span>
                </h2>
                <button onclick="closeModal()" class="kr-close-btn" aria-label="Fechar Modal">×</button>
            </div>

            <div id="kr-modal-body" class="modal-body">
                <p id="kr-modal-text">Clique no botão abaixo para permitir as notificações.</p>
            </div>

            <div class="modal-footer">
                <button id="kr-modal-action" class="btn btn-warning" onclick="handleModalAction()">Permitir
                    Notificações</button>
            </div>
        </div>
    </div>

    <!-- Toast Genérico e Dinâmico -->
    <div id="kr-toast" class="kr-toast hidden">
        <div id="kr-toast-content"></div>
        <button id="kr-toast-close" aria-label="Fechar Toast">✖</button>
    </div>

    <!-- Toast: Já está ativo -->
    <div id="toast-granted" class="kr-toast">
        🔔 Já aceitaste notificações deste site.
    </div>

    <!-- Toast: Recusado -->
    <div id="toast-denied" class="kr-toast">
        ⚠️ Permissão foi recusada.<br>
        Ativa-a manualmente clicando no ícone 🔒 na barra de endereço.
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
    <script src="js/Gerais/notification_API.js" defer></script>
    <script src="js/Info_Suport.js"></script>
    <script src="js/Comunidade_Suporte/Accordion.js"></script>
    <script src="js/Suporte.js"></script>
    <script src="js/Extras/active_notification.js"></script>
    <script src="js/Info_produtos.js"></script>
    <script src="js/Gerais/Geral.js"></script>
    <script src="js/Gerais/cookieConsent.js"></script>
    <script src="js/Comunidade.js"></script>
    <script src="js/Extras/suporte-modal-report.js"></script>
    <script src="js/Traducoes/geral.js"></script>
    <!-- Set user login status for JavaScript -->
    <script>
        // Set the user login status based on PHP session
        document.addEventListener('DOMContentLoaded', function () {
            const isLoggedIn = <?php echo isset($_SESSION['id']) ? 'true' : 'false'; ?>;
            setUserLoginStatus(isLoggedIn);
        });
    </script>
</body>

</html>