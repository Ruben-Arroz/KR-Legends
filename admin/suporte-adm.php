<?php
// ==========================================
// suporte-adm.php - Support Administration
// ==========================================

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

// 2) Verificar permissão (apenas "ADM" e "SUPERADM" podem aceder)
if (!in_array($_SESSION['role'], ['ADM', 'SUPERADM'])) {
    http_response_code(403);
    die('Acesso negado');
}

// 3) Função para formatar tipos de erro
function formatErrorType($errorType)
{
    $errorTypes = [
        'responsividade_visual' => 'Responsividade Visual',
        'funcionalidade' => 'Funcionalidade',
        'latencia' => 'Latência',
        'login_registo' => 'Login ou Registo',
        'visual_grafico' => 'Visual/Gráfico',
        'jogabilidade' => 'Jogabilidade',
        'desempenho' => 'Desempenho',
        'conexao' => 'Conexão'
    ];

    return $errorTypes[$errorType] ?? ucfirst(str_replace('_', ' ', $errorType));
}

// 4) Configuração da paginação
$itemsPerPage = 40;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// 5) Processar filtros e busca com maior precisão
$search = trim($_GET['search'] ?? '');
$error_context = $_GET['error_context'] ?? '';
$error_types = isset($_GET['error_type']) ? (is_array($_GET['error_type']) ? $_GET['error_type'] : [$_GET['error_type']]) : [];
$frequency = $_GET['frequency'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$user_filter = trim($_GET['user_filter'] ?? '');
$status_filter = $_GET['status'] ?? '';

// 6) Construir query com filtros mais precisos
$where_conditions = [];
$params = [];
$param_types = '';

// Busca mais precisa - pesquisa exata em IDs e busca parcial em textos
if (!empty($search)) {
    if (is_numeric($search)) {
        // Se for numérico, busca exata no ID
        $where_conditions[] = "sr.ID_REPORT = ?";
        $params[] = (int) $search;
        $param_types .= 'i';
    } else {
        // Busca parcial em título e descrição com maior precisão
        $where_conditions[] = "(sr.TITLE LIKE ? OR sr.DESCRIPTION LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $param_types .= 'ss';
    }
}

if (!empty($error_context)) {
    $where_conditions[] = "sr.ERROR_CONTEXT = ?";
    $params[] = $error_context;
    $param_types .= 's';
}

// Filtro de múltiplos tipos de erro mais preciso
if (!empty($error_types)) {
    // Remove valores vazios do array
    $error_types = array_filter($error_types);
    if (!empty($error_types)) {
        $placeholders = str_repeat('?,', count($error_types) - 1) . '?';
        $where_conditions[] = "sr.ERROR_TYPE IN ($placeholders)";
        foreach ($error_types as $type) {
            $params[] = $type;
            $param_types .= 's';
        }
    }
}

if (!empty($frequency)) {
    $where_conditions[] = "sr.FREQUENCY = ?";
    $params[] = $frequency;
    $param_types .= 's';
}

// Filtros de data mais precisos
if (!empty($date_from)) {
    $where_conditions[] = "DATE(sr.REPORTED_AT) >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(sr.REPORTED_AT) <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

// Filtro de utilizador mais preciso
if (!empty($user_filter)) {
    if (is_numeric($user_filter)) {
        // Busca exata por ID
        $where_conditions[] = "u.ID = ?";
        $params[] = (int) $user_filter;
        $param_types .= 'i';
    } else {
        // Busca parcial por nome de utilizador ou nome completo
        $where_conditions[] = "(u.KR_USER_NAME LIKE ? OR u.KR_NAME LIKE ?)";
        $user_param = "%$user_filter%";
        $params[] = $user_param;
        $params[] = $user_param;
        $param_types .= 'ss';
    }
}

if (!empty($status_filter)) {
    $where_conditions[] = "sr.STATUS = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// 7) Contar total de registros para paginação
$count_query = "
    SELECT COUNT(*) as total
    FROM KR_SUPPORT_REQUESTS sr
    LEFT JOIN KR_USERS u ON sr.USER_ID = u.ID
    $where_clause
";

$count_stmt = $mysqli->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_records / $itemsPerPage);

// 8) Query principal para buscar pedidos com paginação
$query = "
    SELECT 
        sr.*,
        u.KR_USER_NAME,
        u.KR_NAME,
        u.KR_AVATAR
    FROM KR_SUPPORT_REQUESTS sr
    LEFT JOIN KR_USERS u ON sr.USER_ID = u.ID
    $where_clause
    ORDER BY sr.REPORTED_AT DESC
    LIMIT ? OFFSET ?
";

$stmt = $mysqli->prepare($query);
$pagination_params = $params;
$pagination_param_types = $param_types . 'ii';
$pagination_params[] = $itemsPerPage;
$pagination_params[] = $offset;

if (!empty($pagination_params)) {
    $stmt->bind_param($pagination_param_types, ...$pagination_params);
}
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 9) Estatísticas
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN STATUS = 'nao_resolvido' THEN 1 ELSE 0 END) as nao_resolvido,
        SUM(CASE WHEN STATUS = 'em_processo' THEN 1 ELSE 0 END) as em_processo,
        SUM(CASE WHEN STATUS = 'resolvido' THEN 1 ELSE 0 END) as resolvido
    FROM KR_SUPPORT_REQUESTS sr
    LEFT JOIN KR_USERS u ON sr.USER_ID = u.ID
    $where_clause
";

$stats_stmt = $mysqli->prepare($stats_query);
if (!empty($params)) {
    $stats_stmt->bind_param($param_types, ...$params);
}
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();

// 10) Erro mais frequente
$error_freq_query = "
    SELECT ERROR_TYPE, COUNT(*) as count
    FROM KR_SUPPORT_REQUESTS sr
    LEFT JOIN KR_USERS u ON sr.USER_ID = u.ID
    $where_clause
    GROUP BY ERROR_TYPE
    ORDER BY count DESC
    LIMIT 1
";

$error_freq_stmt = $mysqli->prepare($error_freq_query);
if (!empty($params)) {
    $error_freq_stmt->bind_param($param_types, ...$params);
}
$error_freq_stmt->execute();
$most_frequent_error = $error_freq_stmt->get_result()->fetch_assoc();
$error_freq_stmt->close();

// 11) Verificar se há filtros ativos
$hasActiveFilters = !empty($search) || !empty($error_context) || !empty($error_types) ||
    !empty($frequency) || !empty($date_from) || !empty($date_to) ||
    !empty($user_filter) || !empty($status_filter);

// 12) Função para gerar URL com parâmetros mantidos
function buildPaginationUrl($page, $currentParams)
{
    $params = $currentParams;
    $params['page'] = $page;
    return 'suporte-adm.php?' . http_build_query($params);
}

// Parâmetros atuais para manter nos links de paginação
$currentParams = $_GET;
unset($currentParams['page']); // Remove a página atual para poder definir uma nova
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

    <title>KR Legends - Pedidos de Suporte</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../css/Geral.css" rel="stylesheet">
    <link href="../css/cookieConsent.css" rel="stylesheet">
    <link href="../css/includes/profileMenu.css" rel="stylesheet">
    <link href="../css/includes/modal-logout.css" rel="stylesheet">
    <link href="CSS/geral-adm.css" rel="stylesheet">
    <link href="CSS/suporte-adm.css" rel="stylesheet">
    <link href="CSS/suporte-adm-responsive.css" rel="stylesheet">
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
                                        <a class="dropdown-item d-flex align-items-center" href="../Utilizador/Perfil">
                                            <i class="bi bi-person-circle me-2"></i> Perfil
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center"
                                            href="../Utilizador/stats.php">
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
        <!-- CABEÇALHO LATERAL (SIDEBAR) -->
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
                    <a href="suporte-adm.php" class="adm-sidebar-item disabled">
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

        <!-- Modal Confirmar Exclusão -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content"
                    style="background-color: var(--color-gray-900); border: 1px solid var(--color-gray-700);">
                    <div class="modal-header" style="border-bottom: 1px solid var(--color-gray-700);">
                        <h5 class="modal-title text-white" id="deleteModalLabel">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body text-white">
                        <p>Tem certeza de que deseja excluir este pedido de suporte?</p>
                        <p class="text-warning"><strong>Esta ação não pode ser desfeita!</strong></p>
                        <div id="deleteRequestInfo" class="mt-3 p-3"
                            style="background-color: var(--color-gray-800); border-radius: 6px;">
                            <!-- Informações do pedido serão inseridas aqui -->
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--color-gray-700);">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="bi bi-trash me-1"></i>
                            Excluir Pedido
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <main class="sprtadm-main">
            <!-- Cabeçalho da página -->
            <div class="sprtadm-header">
                <h1 class="sprtadm-title">
                    <i class="bi bi-question-diamond me-2"></i>
                    Pedidos de Suporte
                </h1>
                <p class="sprtadm-subtitle">Gerir e responder aos pedidos de suporte dos utilizadores</p>
            </div>

            <!-- Barra de pesquisa principal -->
            <div class="sprtadm-search-section">
                <form method="GET" class="sprtadm-search-form">
                    <div class="sprtadm-search-wrapper">
                        <div class="sprtadm-input-group">
                            <i class="bi bi-search sprtadm-input-icon"></i>
                            <input type="text" name="search" class="sprtadm-form-control sprtadm-search-input"
                                placeholder="Pesquisar por ID (número exato), título ou descrição..."
                                value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <button type="submit" class="sprtadm-btn sprtadm-btn-primary">
                            <i class="bi bi-search me-1"></i>
                            Pesquisar
                        </button>
                        <button type="button" class="sprtadm-btn sprtadm-btn-outline sprtadm-filter-toggle"
                            id="filterToggle">
                            <i class="bi bi-funnel me-1"></i>
                            Filtros
                            <?php if ($hasActiveFilters): ?>
                                <span
                                    class="sprtadm-filter-badge"><?= count(array_filter([$error_context, $error_types, $frequency, $date_from, $date_to, $user_filter, $status_filter])) ?></span>
                            <?php endif; ?>
                        </button>
                    </div>

                    <!-- Filtros avançados (inicialmente ocultos) -->
                    <div class="sprtadm-advanced-filters" id="advancedFilters"
                        style="<?= $hasActiveFilters ? 'display: block;' : 'display: none;' ?>">
                        <div class="row g-3">
                            <!-- Contexto do erro -->
                            <div class="col-md-3">
                                <label class="sprtadm-filter-label">Contexto</label>
                                <select name="error_context" class="sprtadm-form-select" id="errorContext">
                                    <option value="">Todos os contextos</option>
                                    <option value="website" <?= $error_context === 'website' ? 'selected' : '' ?>>Website
                                    </option>
                                    <option value="game" <?= $error_context === 'game' ? 'selected' : '' ?>>Jogo</option>
                                </select>
                            </div>

                            <!-- Tipo de erro (múltipla seleção com dropdown) -->
                            <div class="col-md-3">
                                <label class="sprtadm-filter-label">Tipo de Erro</label>
                                <div class="dropdown">
                                    <button class="sprtadm-form-select dropdown-toggle" type="button"
                                        id="errorTypeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="errorTypeText">
                                            <?php
                                            if (empty($error_types)) {
                                                echo 'Selecionar tipos...';
                                            } else {
                                                echo count($error_types) . ' tipo(s) selecionado(s)';
                                            }
                                            ?>
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="errorTypeDropdown"
                                        style="width: 100%; max-height: 300px; overflow-y: auto;">
                                        <li>
                                            <div class="dropdown-item">
                                                <input type="checkbox" id="error_type_responsividade_visual"
                                                    name="error_type[]" value="responsividade_visual"
                                                    <?= in_array('responsividade_visual', $error_types) ? 'checked' : '' ?>>
                                                <label for="error_type_responsividade_visual"
                                                    class="ms-2">Responsividade Visual</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item">
                                                <input type="checkbox" id="error_type_funcionalidade"
                                                    name="error_type[]" value="funcionalidade"
                                                    <?= in_array('funcionalidade', $error_types) ? 'checked' : '' ?>>
                                                <label for="error_type_funcionalidade"
                                                    class="ms-2">Funcionalidade</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item">
                                                <input type="checkbox" id="error_type_latencia" name="error_type[]"
                                                    value="latencia" <?= in_array('latencia', $error_types) ? 'checked' : '' ?>>
                                                <label for="error_type_latencia" class="ms-2">Latência</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item">
                                                <input type="checkbox" id="error_type_login_registo" name="error_type[]"
                                                    value="login_registo" <?= in_array('login_registo', $error_types) ? 'checked' : '' ?>>
                                                <label for="error_type_login_registo" class="ms-2">Login ou
                                                    Registo</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item">
                                                <input type="checkbox" id="error_type_visual_grafico"
                                                    name="error_type[]" value="visual_grafico"
                                                    <?= in_array('visual_grafico', $error_types) ? 'checked' : '' ?>>
                                                <label for="error_type_visual_grafico"
                                                    class="ms-2">Visual/Gráfico</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item">
                                                <input type="checkbox" id="error_type_jogabilidade" name="error_type[]"
                                                    value="jogabilidade" <?= in_array('jogabilidade', $error_types) ? 'checked' : '' ?>>
                                                <label for="error_type_jogabilidade" class="ms-2">Jogabilidade</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item">
                                                <input type="checkbox" id="error_type_desempenho" name="error_type[]"
                                                    value="desempenho" <?= in_array('desempenho', $error_types) ? 'checked' : '' ?>>
                                                <label for="error_type_desempenho" class="ms-2">Desempenho</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item">
                                                <input type="checkbox" id="error_type_conexao" name="error_type[]"
                                                    value="conexao" <?= in_array('conexao', $error_types) ? 'checked' : '' ?>>
                                                <label for="error_type_conexao" class="ms-2">Conexão</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Frequência -->
                            <div class="col-md-2">
                                <label class="sprtadm-filter-label">Frequência</label>
                                <select name="frequency" class="sprtadm-form-select">
                                    <option value="">Todas</option>
                                    <option value="sempre" <?= $frequency === 'sempre' ? 'selected' : '' ?>>Sempre</option>
                                    <option value="frequente" <?= $frequency === 'frequente' ? 'selected' : '' ?>>Frequente
                                    </option>
                                    <option value="as_vezes" <?= $frequency === 'as_vezes' ? 'selected' : '' ?>>Às vezes
                                    </option>
                                    <option value="raramente" <?= $frequency === 'raramente' ? 'selected' : '' ?>>Raramente
                                    </option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-2">
                                <label class="sprtadm-filter-label">Status</label>
                                <select name="status" class="sprtadm-form-select">
                                    <option value="">Todos</option>
                                    <option value="nao_resolvido" <?= $status_filter === 'nao_resolvido' ? 'selected' : '' ?>>Não Resolvido</option>
                                    <option value="em_processo" <?= $status_filter === 'em_processo' ? 'selected' : '' ?>>
                                        Em Processo</option>
                                    <option value="resolvido" <?= $status_filter === 'resolvido' ? 'selected' : '' ?>>
                                        Resolvido</option>
                                </select>
                            </div>

                            <!-- Utilizador -->
                            <div class="col-md-2">
                                <label class="sprtadm-filter-label">Utilizador</label>
                                <input type="text" name="user_filter" class="sprtadm-form-control"
                                    placeholder="ID (número) ou nome" value="<?= htmlspecialchars($user_filter) ?>">
                            </div>

                            <!-- Botões de ação -->
                            <div class="col-md-6">
                                <div class="sprtadm-filter-actions">
                                    <button type="submit" class="sprtadm-btn sprtadm-btn-primary">
                                        <i class="bi bi-funnel me-1"></i> Aplicar Filtros
                                    </button>
                                    <a href="suporte-adm.php" class="sprtadm-btn sprtadm-btn-secondary">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Limpar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Estatísticas -->
            <div class="sprtadm-stats-section">
                <div class="row g-3">
                    <div class="col-md-2">
                        <div class="sprtadm-stat-card">
                            <div class="sprtadm-stat-icon">
                                <i class="bi bi-list-ul"></i>
                            </div>
                            <div class="sprtadm-stat-content">
                                <div class="sprtadm-stat-number"><?= $stats['total'] ?></div>
                                <div class="sprtadm-stat-label">Total</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="sprtadm-stat-card sprtadm-stat-danger">
                            <div class="sprtadm-stat-icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="sprtadm-stat-content">
                                <div class="sprtadm-stat-number"><?= $stats['nao_resolvido'] ?></div>
                                <div class="sprtadm-stat-label">Não Resolvidos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="sprtadm-stat-card sprtadm-stat-warning">
                            <div class="sprtadm-stat-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="sprtadm-stat-content">
                                <div class="sprtadm-stat-number"><?= $stats['em_processo'] ?></div>
                                <div class="sprtadm-stat-label">Em Processo</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="sprtadm-stat-card sprtadm-stat-success">
                            <div class="sprtadm-stat-icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="sprtadm-stat-content">
                                <div class="sprtadm-stat-number"><?= $stats['resolvido'] ?></div>
                                <div class="sprtadm-stat-label">Resolvidos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sprtadm-stat-card sprtadm-stat-info">
                            <div class="sprtadm-stat-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div class="sprtadm-stat-content">
                                <div class="sprtadm-stat-number">
                                    <?= formatErrorType($most_frequent_error['ERROR_TYPE'] ?? 'N/A') ?>
                                </div>
                                <div class="sprtadm-stat-label">Erro Mais Frequente</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Pedidos -->
            <div class="sprtadm-requests-section">
                <div class="sprtadm-section-header">
                    <h2 class="sprtadm-section-title">
                        <i class="bi bi-list-check me-2"></i>
                        Pedidos de Suporte (<?= count($requests) ?>)
                    </h2>
                </div>

                <?php if (empty($requests)): ?>
                    <div class="sprtadm-empty-state">
                        <i class="bi bi-inbox"></i>
                        <h3>Nenhum pedido encontrado</h3>
                        <p>Não foram encontrados pedidos de suporte com os filtros aplicados.</p>
                    </div>
                <?php else: ?>
                    <div class="sprtadm-requests-grid">
                        <?php foreach ($requests as $request): ?>
                            <div class="sprtadm-request-card" data-request-id="<?= $request['ID_REPORT'] ?>">
                                <!-- Cabeçalho do card -->
                                <div class="sprtadm-card-header">
                                    <div class="sprtadm-card-title-section">
                                        <h3 class="sprtadm-card-title"><?= htmlspecialchars($request['TITLE']) ?></h3>
                                        <div class="sprtadm-card-badges">
                                            <span class="sprtadm-badge sprtadm-badge-<?= $request['ERROR_CONTEXT'] ?>">
                                                <?= ucfirst($request['ERROR_CONTEXT']) ?>
                                            </span>
                                            <span class="sprtadm-badge sprtadm-badge-type">
                                                <?= formatErrorType($request['ERROR_TYPE']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="sprtadm-card-id">#<?= $request['ID_REPORT'] ?></div>
                                </div>

                                <!-- Resumo da descrição -->
                                <div class="sprtadm-card-description">
                                    <?= htmlspecialchars(substr($request['DESCRIPTION'], 0, 150)) ?>
                                    <?= strlen($request['DESCRIPTION']) > 150 ? '...' : '' ?>
                                </div>

                                <!-- Metadados -->
                                <div class="sprtadm-card-meta">
                                    <div class="sprtadm-meta-item">
                                        <i class="bi bi-person"></i>
                                        <span>
                                            <?php if ($request['USER_ID']): ?>
                                                <?= htmlspecialchars($request['KR_NAME'] ?: $request['KR_USER_NAME']) ?>
                                            <?php else: ?>
                                                Utilizador Anónimo
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="sprtadm-meta-item">
                                        <i class="bi bi-calendar"></i>
                                        <span><?= date('d/m/Y H:i', strtotime($request['REPORTED_AT'])) ?></span>
                                    </div>
                                    <div class="sprtadm-meta-item">
                                        <i class="bi bi-arrow-repeat"></i>
                                        <span><?= ucfirst(str_replace('_', ' ', $request['FREQUENCY'])) ?></span>
                                    </div>
                                </div>

                                <!-- Ações -->
                                <div class="sprtadm-card-actions">
                                    <button class="sprtadm-status-btn sprtadm-status-<?= $request['STATUS'] ?>"
                                        onclick="toggleStatus(<?= $request['ID_REPORT'] ?>, '<?= $request['STATUS'] ?>')">
                                        <i
                                            class="bi bi-<?= $request['STATUS'] === 'nao_resolvido' ? 'exclamation-triangle' : ($request['STATUS'] === 'em_processo' ? 'clock' : 'check-circle') ?>"></i>
                                        <?= ucfirst(str_replace('_', ' ', $request['STATUS'])) ?>
                                    </button>
                                    <a href="suporte-adm-detail.php?id=<?= $request['ID_REPORT'] ?>"
                                        class="sprtadm-btn sprtadm-btn-outline">
                                        <i class="bi bi-eye me-1"></i>
                                        Ver Detalhes
                                    </a>
                                    <?php if ($_SESSION['role'] === 'SUPERADM'): ?>
                                        <button class="sprtadm-btn sprtadm-btn-danger"
                                            onclick="confirmDelete(<?= $request['ID_REPORT'] ?>, '<?= htmlspecialchars($request['TITLE'], ENT_QUOTES) ?>')">
                                            <i class="bi bi-trash me-1"></i>
                                            Excluir
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Paginação -->
                    <?php if ($total_pages > 1): ?>
                        <div class="sprtadm-pagination">
                            <!-- Botão Anterior -->
                            <?php if ($currentPage > 1): ?>
                                <a href="<?= buildPaginationUrl($currentPage - 1, $currentParams) ?>"
                                    class="sprtadm-pagination-btn">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            <?php else: ?>
                                <button class="sprtadm-pagination-btn" disabled>
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            <?php endif; ?>

                            <!-- Números das páginas -->
                            <?php
                            $start = max(1, $currentPage - 2);
                            $end = min($total_pages, $currentPage + 2);

                            // Sempre mostrar primeira página
                            if ($start > 1):
                                ?>
                                <a href="<?= buildPaginationUrl(1, $currentParams) ?>" class="sprtadm-pagination-btn">1</a>
                                <?php if ($start > 2): ?>
                                    <span class="sprtadm-pagination-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Páginas do intervalo atual -->
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <button class="sprtadm-pagination-btn active"><?= $i ?></button>
                                <?php else: ?>
                                    <a href="<?= buildPaginationUrl($i, $currentParams) ?>" class="sprtadm-pagination-btn"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <!-- Sempre mostrar última página -->
                            <?php if ($end < $total_pages): ?>
                                <?php if ($end < $total_pages - 1): ?>
                                    <span class="sprtadm-pagination-ellipsis">...</span>
                                <?php endif; ?>
                                <a href="<?= buildPaginationUrl($total_pages, $currentParams) ?>"
                                    class="sprtadm-pagination-btn"><?= $total_pages ?></a>
                            <?php endif; ?>

                            <!-- Botão Próximo -->
                            <?php if ($currentPage < $total_pages): ?>
                                <a href="<?= buildPaginationUrl($currentPage + 1, $currentParams) ?>"
                                    class="sprtadm-pagination-btn">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <button class="sprtadm-pagination-btn" disabled>
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            <?php endif; ?>

                            <!-- Informações da paginação -->
                            <div class="sprtadm-pagination-info">
                                Mostrando
                                <?= ($currentPage - 1) * $itemsPerPage + 1 ?>-<?= min($currentPage * $itemsPerPage, $total_records) ?>
                                de <?= $total_records ?> resultados
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
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
    <script src="JS\suporte-adm.js"></script>
    <script src="../js/includes/profileMenu.js"></script>
    <script src="../js/Gerais/Geral.js"></script>
    <script src="../js/Gerais/cookieConsent.js"></script>
    <script src="../js/Gerais/notification_API.js" defer></script>
</body>

</html>