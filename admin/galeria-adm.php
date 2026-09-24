<?php
// ==========================================
// galeria-adm.php - Galery Administration
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

// 3) Função para formatar tamanho de ficheiro
function formatFileSize($bytes)
{
    if ($bytes === 0)
        return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round(($bytes / pow($k, $i)), 2) . ' ' . $sizes[$i];
}

// 4) Buscar estatísticas da galeria
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN GL_IS_PUBLIC = 1 THEN 1 ELSE 0 END) as publicos,
        SUM(CASE WHEN GL_IS_PUBLIC = 0 THEN 1 ELSE 0 END) as privados,
        GL_CATEGORY as categoria_mais_usada,
        COUNT(*) as count_categoria
    FROM KR_GALERIA
    GROUP BY GL_CATEGORY
    ORDER BY count_categoria DESC
    LIMIT 1
";
$stats_result = $mysqli->query($stats_query);
$categoria_mais_usada = $stats_result->fetch_assoc();

// Buscar estatísticas gerais
$general_stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN GL_IS_PUBLIC = 1 THEN 1 ELSE 0 END) as publicos,
        SUM(CASE WHEN GL_IS_PUBLIC = 0 THEN 1 ELSE 0 END) as privados
    FROM KR_GALERIA
";
$general_stats_result = $mysqli->query($general_stats_query);
$stats = $general_stats_result->fetch_assoc();

// 5) Paginação e filtros
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 24;
$offset = ($page - 1) * $items_per_page;

// Construir query com filtros
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($_GET['search'])) {
    $where_conditions[] = "(GL_ID LIKE ? OR GL_TITLE LIKE ?)";
    $search_term = '%' . $_GET['search'] . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'ss';
}

if (!empty($_GET['category']) && is_array($_GET['category'])) {
    $category_placeholders = str_repeat('?,', count($_GET['category']) - 1) . '?';
    $where_conditions[] = "GL_CATEGORY IN ($category_placeholders)";
    foreach ($_GET['category'] as $cat) {
        $params[] = $cat;
        $param_types .= 's';
    }
}

if (isset($_GET['is_public']) && $_GET['is_public'] !== '') {
    $where_conditions[] = "GL_IS_PUBLIC = ?";
    $params[] = intval($_GET['is_public']);
    $param_types .= 'i';
}

if (!empty($_GET['date_from'])) {
    $where_conditions[] = "DATE(GL_CREATED_AT) >= ?";
    $params[] = $_GET['date_from'];
    $param_types .= 's';
}

if (!empty($_GET['date_to'])) {
    $where_conditions[] = "DATE(GL_CREATED_AT) <= ?";
    $params[] = $_GET['date_to'];
    $param_types .= 's';
}

if (!empty($_GET['extension'])) {
    // Para filtrar por extensão, precisamos verificar os ficheiros existentes
    $extension_filter = $_GET['extension'];
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Query para contar total de itens
$count_query = "SELECT COUNT(*) as total FROM KR_GALERIA $where_clause";
$count_stmt = $mysqli->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Query principal para buscar itens
$order_by = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';
$main_query = "
    SELECT g.*, u.KR_NAME, u.KR_USER_NAME 
    FROM KR_GALERIA g 
    LEFT JOIN KR_USERS u ON g.GL_USER_ID = u.ID 
    $where_clause 
    ORDER BY g.GL_ID $order_by 
    LIMIT ? OFFSET ?
";

$stmt = $mysqli->prepare($main_query);
$params[] = $items_per_page;
$params[] = $offset;
$param_types .= 'ii';

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$gallery_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Filtrar por extensão se especificado
if (!empty($_GET['extension'])) {
    $extension_filter = $_GET['extension'];
    $filtered_items = [];
    
    foreach ($gallery_items as $item) {
        $file_path = "../Imagens/Galeria/item_" . $item['GL_ID'];
        $file_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];
        
        foreach ($file_extensions as $ext) {
            if (file_exists($file_path . '.' . $ext)) {
                if ($ext === $extension_filter || 
                    ($extension_filter === 'jpg' && $ext === 'jpeg') ||
                    ($extension_filter === 'jpeg' && $ext === 'jpg')) {
                    $filtered_items[] = $item;
                }
                break;
            }
        }
    }
    $gallery_items = $filtered_items;
    $total_items = count($gallery_items);
    $total_pages = ceil($total_items / $items_per_page);
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

    <title>KR Legends - Gerir Galeria</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../css/Geral.css" rel="stylesheet">
    <link href="../css/cookieConsent.css" rel="stylesheet">
    <link href="../css/includes/profileMenu.css" rel="stylesheet">
    <link href="../css/includes/modal-logout.css" rel="stylesheet">
    <link href="CSS/geral-adm.css" rel="stylesheet">
    <link href="CSS/galeria-adm.css" rel="stylesheet">
    <link href="CSS/galeria-adm-responsive.css" rel="stylesheet">
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
                    <a href="galeria-adm.php" class="adm-sidebar-item disabled">
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
        <!-- Modal Confirmar Eliminação -->
<div class="modal fade" id="gl-deleteModal" tabindex="-1" aria-labelledby="gl-deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog gl-modal-dialog">
    <div class="modal-content gl-modal-content">
      <div class="modal-header gl-modal-header">
        <h5 class="modal-title" id="gl-deleteModalLabel">
          <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
          Confirmar Eliminação
        </h5>
        <button type="button" class="btn-close gl-btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body gl-modal-body">
        <p>Tem a certeza que deseja eliminar este item da galeria? Esta ação não pode ser desfeita.</p>
      </div>
      <div class="modal-footer gl-modal-footer">
        <button type="button" class="gl-btn gl-btn-secondary" data-bs-dismiss="modal">
          Cancelar
        </button>
        <button type="button" id="gl-confirmDelete" class="gl-btn gl-btn-danger">
          <i class="bi bi-trash me-2"></i>Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

       <!-- Modal Adicionar Item -->
        <div class="modal fade" id="gl-addItemModal" tabindex="-1" aria-labelledby="gl-addItemModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg gl-modal-dialog">
                <div class="modal-content gl-modal-content">
                    <div class="modal-header gl-modal-header">
                        <h5 class="modal-title" id="gl-addItemModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>Adicionar Item à Galeria
                        </h5>
                        <button type="button" class="btn-close gl-btn-close" data-bs-dismiss="modal"
                            aria-label="Fechar"></button>
                    </div>
                    <form id="gl-addItemForm" enctype="multipart/form-data" method="POST">
                        <div class="modal-body gl-modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="gl-form-group">
                                        <label for="gl-addFile" class="gl-form-label gl-required">
                                            <i class="bi bi-cloud-upload me-2"></i>Ficheiro (Máx: 35MB)
                                        </label>
                                        <input type="file" class="gl-form-control" id="gl-addFile" name="file"
                                            accept="image/*,video/*" required>
                                        <div class="gl-file-info" id="gl-addFileInfo"></div>
                                        <div class="gl-form-text">Formatos suportados: JPG, PNG, GIF, WEBP, MP4, WEBM</div>
                                    </div>
                                    <div class="gl-form-group">
                                        <label for="gl-addTitle" class="gl-form-label gl-required">Título</label>
                                        <input type="text" class="gl-form-control" id="gl-addTitle" name="title"
                                            maxlength="150" required>
                                        <div class="gl-form-text">Máximo 150 caracteres</div>
                                    </div>
                                    <div class="gl-form-group">
                                        <label for="gl-addCategory" class="gl-form-label gl-required">Categoria</label>
                                        <select class="gl-form-control" id="gl-addCategory" name="category" required>
                                            <option value="">Selecionar categoria</option>
                                            <option value="carros">Carros</option>
                                            <option value="pistas">Pistas</option>
                                            <option value="corridas">Corridas</option>
                                            <option value="eventos">Eventos</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="gl-form-group">
                                        <label for="gl-addDescription" class="gl-form-label">Descrição</label>
                                        <textarea class="gl-form-control" id="gl-addDescription" name="description"
                                            rows="4" maxlength="500" placeholder="Descrição opcional do item..."></textarea>
                                        <div class="gl-form-text">Máximo 500 caracteres</div>
                                    </div>
                                    <div class="gl-form-group">
                                        <label for="gl-addAlt" class="gl-form-label">Texto Alternativo</label>
                                        <input type="text" class="gl-form-control" id="gl-addAlt" name="alt"
                                            maxlength="255" placeholder="Texto para acessibilidade...">
                                        <div class="gl-form-text">Texto alternativo para acessibilidade</div>
                                    </div>
                                    <div class="gl-form-group">
                                        <div class="gl-form-check">
                                            <input type="checkbox" class="gl-form-check-input" id="gl-addIsPublic"
                                                name="is_public" value="1" checked>
                                            <label class="gl-form-check-label" for="gl-addIsPublic">
                                                <i class="bi bi-eye me-2"></i>Tornar público
                                            </label>
                                        </div>
                                        <div class="gl-form-text">Itens públicos aparecerão na galeria do site</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer gl-modal-footer">
                            <button type="button" class="gl-btn gl-btn-secondary"
                                data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="gl-btn gl-btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Adicionar Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Item -->
        <div class="modal fade" id="gl-editItemModal" tabindex="-1" aria-labelledby="gl-editItemModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg gl-modal-dialog">
                <div class="modal-content gl-modal-content">
                    <div class="modal-header gl-modal-header">
                        <h5 class="modal-title" id="gl-editItemModalLabel">
                            <i class="bi bi-pencil-square me-2"></i>Editar Item da Galeria
                        </h5>
                        <button type="button" class="btn-close gl-btn-close" data-bs-dismiss="modal"
                            aria-label="Fechar"></button>
                    </div>
                    <form id="gl-editItemForm" enctype="multipart/form-data">
                        <input type="hidden" id="gl-editItemId" name="item_id">
                        <div class="modal-body gl-modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="gl-form-group">
                                        <label for="gl-editFile" class="gl-form-label">
                                            <i class="bi bi-cloud-upload me-2"></i>Substituir Ficheiro (Opcional)
                                        </label>
                                        <input type="file" class="gl-form-control" id="gl-editFile" name="file"
                                            accept="image/*,video/*">
                                        <div class="gl-file-info" id="gl-editFileInfo"></div>
                                        <div class="gl-form-text">Deixar vazio para manter o ficheiro atual</div>
                                    </div>
                                    <div class="gl-form-group">
                                        <label for="gl-editTitle" class="gl-form-label gl-required">Título</label>
                                        <input type="text" class="gl-form-control" id="gl-editTitle" name="title"
                                            maxlength="150" required>
                                    </div>
                                    <div class="gl-form-group">
                                        <label for="gl-editCategory" class="gl-form-label gl-required">Categoria</label>
                                        <select class="gl-form-control" id="gl-editCategory" name="category" required>
                                            <option value="carros">Carros</option>
                                            <option value="pistas">Pistas</option>
                                            <option value="corridas">Corridas</option>
                                            <option value="eventos">Eventos</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="gl-form-group">
                                        <label for="gl-editDescription" class="gl-form-label">Descrição</label>
                                        <textarea class="gl-form-control" id="gl-editDescription" name="description"
                                            rows="4" maxlength="500"></textarea>
                                    </div>
                                    <div class="gl-form-group">
                                        <label for="gl-editAlt" class="gl-form-label">Texto Alternativo</label>
                                        <input type="text" class="gl-form-control" id="gl-editAlt" name="alt"
                                            maxlength="255">
                                    </div>
                                    <div class="gl-form-group">
                                        <div class="gl-form-check">
                                            <input type="checkbox" class="gl-form-check-input" id="gl-editIsPublic"
                                                name="is_public" value="1">
                                            <label class="gl-form-check-label" for="gl-editIsPublic">
                                                <i class="bi bi-eye me-2"></i>Público
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer gl-modal-footer">
                            <button type="button" class="gl-btn gl-btn-secondary"
                                data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="gl-btn gl-btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Guardar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <main class="gl-main">
            <!-- Cabeçalho da Página -->
            <div class="gl-header">
                <div class="gl-header-content">
                    <div class="gl-header-title">
                        <i class="bi bi-images gl-header-icon"></i>
                        <div>
                            <h1 class="gl-title">Gestão da Galeria</h1>
                            <p class="gl-subtitle">Gerir e organizar os itens da galeria multimédia</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="gl-stats-container">
                <div class="gl-stats-grid">
                    <div class="gl-stat-card gl-stat-total">
                        <div class="gl-stat-icon">
                            <i class="bi bi-collection"></i>
                        </div>
                        <div class="gl-stat-content">
                            <div class="gl-stat-number"><?= $stats['total'] ?></div>
                            <div class="gl-stat-label">Total de Itens</div>
                        </div>
                    </div>
                    <div class="gl-stat-card gl-stat-public">
                        <div class="gl-stat-icon">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="gl-stat-content">
                            <div class="gl-stat-number"><?= $stats['publicos'] ?></div>
                            <div class="gl-stat-label">Públicos</div>
                        </div>
                    </div>
                    <div class="gl-stat-card gl-stat-private">
                        <div class="gl-stat-icon">
                            <i class="bi bi-eye-slash"></i>
                        </div>
                        <div class="gl-stat-content">
                            <div class="gl-stat-number"><?= $stats['privados'] ?></div>
                            <div class="gl-stat-label">Privados</div>
                        </div>
                    </div>
                    <div class="gl-stat-card gl-stat-category">
                        <div class="gl-stat-icon">
                            <i class="bi bi-star"></i>
                        </div>
                        <div class="gl-stat-content">
                            <div class="gl-stat-number"><?= $categoria_mais_usada ? ucfirst($categoria_mais_usada['categoria_mais_usada']) : 'N/A' ?></div>
                            <div class="gl-stat-label">Categoria Mais Usada</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de Pesquisa e Ações -->
            <div class="gl-search-section">
                <form class="gl-search-form" method="GET">
                    <div class="gl-search-input-group">
                        <i class="bi bi-search gl-search-icon"></i>
                        <input type="text" class="gl-search-input" name="search"
                            placeholder="Pesquisar por ID ou título..."
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit" class="gl-search-btn">
                            <i class="bi bi-search"></i>
                            Pesquisar
                        </button>
                    </div>
                </form>
                <div class="gl-action-buttons">
                    <button type="button" class="gl-btn gl-btn-outline" id="gl-toggleFilters">
                        <i class="bi bi-funnel"></i>
                        Filtros
                    </button>
                    <button type="button" class="gl-btn gl-btn-primary" data-bs-toggle="modal"
                        data-bs-target="#gl-addItemModal">
                        <i class="bi bi-plus-circle"></i>
                        Adicionar Item
                    </button>
                </div>
            </div>

            <!-- Seção de Filtros -->
            <div class="gl-filters-section" id="gl-filtersSection">
                <div class="gl-filters-container">
                    <div class="gl-filters-header">
                        <h3 class="gl-filters-title">
                            <i class="bi bi-funnel me-2"></i>Filtros Avançados
                        </h3>
                    </div>
                    <form class="gl-filters-form" method="GET">
                        <!-- Manter pesquisa atual -->
                        <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        
                        <div class="gl-filters-grid">
                            <div class="gl-filter-group">
                                <label class="gl-filter-label">Categorias</label>
                                <div class="gl-checkbox-group">
                                    <?php
                                    $categories = ['carros', 'pistas', 'corridas', 'eventos'];
                                    $selectedCategories = $_GET['category'] ?? [];
                                    ?>
                                    <?php foreach ($categories as $category): ?>
                                        <div class="gl-form-check">
                                            <input type="checkbox" class="gl-form-check-input" 
                                                id="filter-<?= $category ?>" 
                                                name="category[]" 
                                                value="<?= $category ?>"
                                                <?= in_array($category, $selectedCategories) ? 'checked' : '' ?>>
                                            <label class="gl-form-check-label" for="filter-<?= $category ?>">
                                                <?= ucfirst($category) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="gl-filter-group">
                                <label for="filter-visibility" class="gl-filter-label">Visibilidade</label>
                                <select class="gl-form-control" id="filter-visibility" name="is_public">
                                    <option value="">Todos</option>
                                    <option value="1" <?= ($_GET['is_public'] ?? '') === '1' ? 'selected' : '' ?>>Públicos</option>
                                    <option value="0" <?= ($_GET['is_public'] ?? '') === '0' ? 'selected' : '' ?>>Privados</option>
                                </select>
                            </div>

                            <div class="gl-filter-group">
                                <label for="filter-extension" class="gl-filter-label">Extensão do Ficheiro</label>
                                <select class="gl-form-control" id="filter-extension" name="extension">
                                    <option value="">Todas</option>
                                    <option value="jpg" <?= ($_GET['extension'] ?? '') === 'jpg' ? 'selected' : '' ?>>JPG/JPEG</option>
                                    <option value="png" <?= ($_GET['extension'] ?? '') === 'png' ? 'selected' : '' ?>>PNG</option>
                                    <option value="gif" <?= ($_GET['extension'] ?? '') === 'gif' ? 'selected' : '' ?>>GIF</option>
                                    <option value="webp" <?= ($_GET['extension'] ?? '') === 'webp' ? 'selected' : '' ?>>WEBP</option>
                                    <option value="mp4" <?= ($_GET['extension'] ?? '') === 'mp4' ? 'selected' : '' ?>>MP4</option>
                                    <option value="webm" <?= ($_GET['extension'] ?? '') === 'webm' ? 'selected' : '' ?>>WEBM</option>
                                </select>
                            </div>

                            <div class="gl-filter-group">
                                <label for="filter-date-from" class="gl-filter-label">Data de Criação (De)</label>
                                <input type="date" class="gl-form-control" id="filter-date-from" name="date_from"
                                    value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                            </div>

                            <div class="gl-filter-group">
                                <label for="filter-date-to" class="gl-filter-label">Data de Criação (Até)</label>
                                <input type="date" class="gl-form-control" id="filter-date-to" name="date_to"
                                    value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                            </div>

                            <div class="gl-filter-group">
                                <label for="filter-order" class="gl-filter-label">Ordenar por ID</label>
                                <select class="gl-form-control" id="filter-order" name="order">
                                    <option value="asc" <?= ($_GET['order'] ?? 'asc') === 'asc' ? 'selected' : '' ?>>Crescente</option>
                                    <option value="desc" <?= ($_GET['order'] ?? '') === 'desc' ? 'selected' : '' ?>>Decrescente</option>
                                </select>
                            </div>
                        </div>

                        <div class="gl-filters-actions">
                            <button type="button" class="gl-btn gl-btn-secondary" onclick="clearFilters()">
                                <i class="bi bi-x-circle me-2"></i>Limpar Filtros
                            </button>
                            <button type="submit" class="gl-btn gl-btn-primary">
                                <i class="bi bi-funnel me-2"></i>Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Itens -->
            <div class="gl-content-section">
                <div class="gl-section-header">
                    <h2 class="gl-section-title">
                        <i class="bi bi-grid-3x3-gap"></i>
                        Itens da Galeria (<?= $total_items ?>)
                    </h2>
                </div>

                <?php if (empty($gallery_items)): ?>
                    <div class="gl-empty-state">
                        <div class="gl-empty-icon">
                            <i class="bi bi-images"></i>
                        </div>
                        <h3 class="gl-empty-title">Nenhum item encontrado</h3>
                        <p class="gl-empty-text">Não foram encontrados itens da galeria com os filtros aplicados.</p>
                        <button type="button" class="gl-btn gl-btn-primary" data-bs-toggle="modal"
                            data-bs-target="#gl-addItemModal">
                            <i class="bi bi-plus-circle"></i>
                            Adicionar Primeiro Item
                        </button>
                    </div>
                <?php else: ?>
                    <div class="gl-items-grid">
                        <?php foreach ($gallery_items as $item): ?>
                            <div class="gl-item-card" data-item-id="<?= $item['GL_ID'] ?>">
                                <div class="gl-item-media">
                                    <?php
                                    $file_path = "../Imagens/Galeria/item_" . $item['GL_ID'];
                                    $file_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];
                                    $file_found = false;
                                    $file_extension = '';
                                    $file_size = 0;

                                    foreach ($file_extensions as $ext) {
                                        if (file_exists($file_path . '.' . $ext)) {
                                            $file_found = true;
                                            $file_extension = $ext;
                                            $file_size = filesize($file_path . '.' . $ext);
                                            break;
                                        }
                                    }

                                    if ($file_found):
                                        if (in_array($file_extension, ['mp4', 'webm'])):
                                            ?>
                                            <video class="gl-item-video" controls>
                                                <source src="<?= $file_path . '.' . $file_extension ?>"
                                                    type="video/<?= $file_extension ?>">
                                                Seu navegador não suporta vídeo.
                                            </video>
                                        <?php else: ?>
                                            <img class="gl-item-image" src="<?= $file_path . '.' . $file_extension ?>"
                                                alt="<?= htmlspecialchars($item['GL_ALT'] ?? $item['GL_TITLE']) ?>"
                                                loading="lazy">
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="gl-item-placeholder">
                                            <i class="bi bi-image"></i>
                                            <span>Ficheiro não encontrado</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="gl-item-overlay">
                                        <div class="gl-item-actions">
                                            <button type="button" class="gl-item-action-btn gl-btn-edit"
                                                onclick="editItem(<?= $item['GL_ID'] ?>)" title="Editar Item">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="gl-item-action-btn gl-btn-delete"
                                                onclick="confirmDeleteItem(<?= $item['GL_ID'] ?>)" title="Eliminar Item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="gl-item-content">
                                    <div class="gl-item-header">
                                        <h3 class="gl-item-title"><?= htmlspecialchars($item['GL_TITLE']) ?></h3>
                                        <div class="gl-item-badges">
                                            <span class="gl-badge gl-badge-category gl-badge-<?= $item['GL_CATEGORY'] ?>">
                                                <i class="bi bi-tag"></i>
                                                <?= ucfirst($item['GL_CATEGORY']) ?>
                                            </span>
                                            <span
                                                class="gl-badge <?= $item['GL_IS_PUBLIC'] ? 'gl-badge-public' : 'gl-badge-private' ?>">
                                                <i class="bi bi-<?= $item['GL_IS_PUBLIC'] ? 'eye' : 'eye-slash' ?>"></i>
                                                <?= $item['GL_IS_PUBLIC'] ? 'Público' : 'Privado' ?>
                                            </span>
                                        </div>
                                    </div>

                                    <?php if (!empty($item['GL_DESCRIPTION'])): ?>
                                        <p class="gl-item-description"><?= htmlspecialchars($item['GL_DESCRIPTION']) ?></p>
                                    <?php endif; ?>

                                    <div class="gl-item-meta">
                                        <div class="gl-item-meta-row">
                                            <span class="gl-meta-label">
                                                <i class="bi bi-hash"></i>
                                                ID:
                                            </span>
                                            <span class="gl-meta-value">#<?= $item['GL_ID'] ?></span>
                                        </div>
                                        <div class="gl-item-meta-row">
                                            <span class="gl-meta-label">
                                                <i class="bi bi-person"></i>
                                                Autor:
                                            </span>
                                            <span class="gl-meta-value">
                                                <?= htmlspecialchars($item['KR_NAME'] ?: $item['KR_USER_NAME'] ?: 'Sistema') ?>
                                            </span>
                                        </div>
                                        <div class="gl-item-meta-row">
                                            <span class="gl-meta-label">
                                                <i class="bi bi-calendar-plus"></i>
                                                Criado:
                                            </span>
                                            <span class="gl-meta-value">
                                                <?= date('d/m/Y H:i', strtotime($item['GL_CREATED_AT'])) ?>
                                            </span>
                                        </div>
                                        <?php if ($item['GL_UPDATED_AT'] !== $item['GL_CREATED_AT']): ?>
                                            <div class="gl-item-meta-row">
                                                <span class="gl-meta-label">
                                                    <i class="bi bi-calendar-check"></i>
                                                    Atualizado:
                                                </span>
                                                <span class="gl-meta-value">
                                                    <?= date('d/m/Y H:i', strtotime($item['GL_UPDATED_AT'])) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($file_found): ?>
                                            <div class="gl-item-meta-row">
                                                <span class="gl-meta-label">
                                                    <i class="bi bi-file-earmark"></i>
                                                    Ficheiro:
                                                </span>
                                                <span class="gl-meta-value">
                                                    <?= strtoupper($file_extension) ?> • <?= formatFileSize($file_size) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginação -->
                    <?php if ($total_pages > 1): ?>
                        <div class="gl-pagination">
                            <?php
                            $current_page = $page;
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            // Construir URL base para paginação
                            $base_url = '?' . http_build_query(array_merge($_GET, ['page' => '']));
                            $base_url = rtrim($base_url, '=');
                            ?>

                            <!-- Botão Anterior -->
                            <?php if ($current_page > 1): ?>
                                <a href="<?= $base_url ?>=<?= $current_page - 1 ?>" class="gl-pagination-btn">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            <?php else: ?>
                                <button class="gl-pagination-btn" disabled>
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            <?php endif; ?>

                            <!-- Primeira página -->
                            <?php if ($start_page > 1): ?>
                                <a href="<?= $base_url ?>=1" class="gl-pagination-btn">1</a>
                                <?php if ($start_page > 2): ?>
                                    <span class="gl-pagination-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Páginas do meio -->
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <?php if ($i == $current_page): ?>
                                    <button class="gl-pagination-btn active"><?= $i ?></button>
                                <?php else: ?>
                                    <a href="<?= $base_url ?>=<?= $i ?>" class="gl-pagination-btn"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <!-- Última página -->
                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <span class="gl-pagination-ellipsis">...</span>
                                <?php endif; ?>
                                <a href="<?= $base_url ?>=<?= $total_pages ?>" class="gl-pagination-btn"><?= $total_pages ?></a>
                            <?php endif; ?>

                            <!-- Botão Próximo -->
                            <?php if ($current_page < $total_pages): ?>
                                <a href="<?= $base_url ?>=<?= $current_page + 1 ?>" class="gl-pagination-btn">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <button class="gl-pagination-btn" disabled>
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            <?php endif; ?>

                            <div class="gl-pagination-info">
                                Mostrando
                                <?= ($page - 1) * $items_per_page + 1 ?>-<?= min($page * $items_per_page, $total_items) ?> de
                                <?= $total_items ?> resultados
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
    <script src="JS\galeria-adm.js"></script>
    <script src="../js/includes/profileMenu.js"></script>
    <script src="../js/Gerais/Geral.js"></script>
    <script src="../js/Gerais/cookieConsent.js"></script>
    <script src="../js/Gerais/notification_API.js" defer></script>
</body>
</html>