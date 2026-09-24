<?php
/**
 * suporte-adm-detail.php
 * Página de detalhes de um pedido de suporte específico
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();

// 1) Incluir conexão MySQLi
require_once __DIR__ . '/../../databaseconnect.php';

// 2) Verificar permissão
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

// 4) Obter ID do pedido
$requestId = (int) ($_GET['id'] ?? 0);

if ($requestId <= 0) {
    http_response_code(400);
    die('ID do pedido inválido');
}

// 5) Buscar dados do pedido
$query = "
    SELECT 
        sr.*,
        u.KR_USER_NAME,
        u.KR_NAME,
        u.KR_EMAIL_USER,
        u.KR_AVATAR,
        u.KR_BIO,
        u.KR_COUNTRY,
        u.KR_CITY,
        u.KR_DATE_OF_BIRTH,
        u.KR_GENERO,
        u.KR_BANNER,
        u.KR_CREATED_AT,
        u.KR_LAST_LOGIN,
        u.ROBLOX_USERNAME,
        us.EMAIL_SOCIAL,
        us.WHATSAPP,
        us.YOUTUBE,
        us.INSTAGRAM,
        us.TIKTOK,
        us.FACEBOOK,
        us.LINKEDIN,
        us.GITHUB,
        us.TWITTER
    FROM KR_SUPPORT_REQUESTS sr
    LEFT JOIN KR_USERS u ON sr.USER_ID = u.ID
    LEFT JOIN KR_USER_SOCIALS us ON sr.USER_ID = us.SOCIAL_ID
    WHERE sr.ID_REPORT = ?
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $requestId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die('Pedido de suporte não encontrado');
}

$request = $result->fetch_assoc();
$stmt->close();

// 6) Verificar se pode editar (apenas SUPERADM)
$canEdit = $_SESSION['role'] === 'SUPERADM';

// 7) Processar atualização se for POST e SUPERADM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canEdit) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $reproSteps = trim($_POST['repro_steps'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if (!empty($title) && !empty($description) && in_array($status, ['nao_resolvido', 'em_processo', 'resolvido'])) {
        $updateStmt = $mysqli->prepare("
            UPDATE KR_SUPPORT_REQUESTS 
            SET TITLE = ?, DESCRIPTION = ?, REPRO_STEPS = ?, STATUS = ?
            WHERE ID_REPORT = ?
        ");
        $updateStmt->bind_param("ssssi", $title, $description, $reproSteps, $status, $requestId);

        if ($updateStmt->execute()) {
            $successMessage = "Pedido atualizado com sucesso!";
            // Recarregar dados
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $request = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            $errorMessage = "Erro ao atualizar pedido: " . $updateStmt->error;
        }
        $updateStmt->close();
    } else {
        $errorMessage = "Preencha todos os campos obrigatórios.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KR Legends - Detalhes do Pedido #<?= $requestId ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../css/Geral.css" rel="stylesheet">
    <link href="CSS/geral-adm.css" rel="stylesheet">
    <link href="CSS/suporte-adm.css" rel="stylesheet">
    <link href="CSS/suporte-adm-responsive.css" rel="stylesheet">
</head>

<body>
    <div class="sprtadm-detail-container">
        <!-- Cabeçalho -->
        <div class="sprtadm-detail-header">
            <div class="sprtadm-detail-nav">
                <a href="suporte-adm.php" class="sprtadm-btn sprtadm-btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Voltar à Lista
                </a>
            </div>

            <div class="sprtadm-detail-title-section">
                <h1 class="sprtadm-detail-title">
                    <i class="bi bi-question-diamond me-2"></i>
                    Pedido de Suporte #<?= $requestId ?>
                </h1>
                <div class="sprtadm-detail-badges">
                    <span class="sprtadm-badge sprtadm-badge-<?= $request['ERROR_CONTEXT'] ?>">
                        <?= ucfirst($request['ERROR_CONTEXT']) ?>
                    </span>
                    <span class="sprtadm-badge sprtadm-badge-type">
                        <?= formatErrorType($request['ERROR_TYPE']) ?>
                    </span>
                    <span class="sprtadm-status-badge sprtadm-status-<?= $request['STATUS'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $request['STATUS'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Mensagens -->
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= htmlspecialchars($successMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Coluna Principal - Dados do Pedido -->
            <div class="col-xl-8 col-lg-7">
                <div class="sprtadm-detail-card">
                    <div class="sprtadm-detail-card-header">
                        <h2 class="sprtadm-detail-card-title">
                            <i class="bi bi-file-text me-2"></i>
                            Detalhes do Pedido
                        </h2>
                        <?php if ($canEdit): ?>
                            <button type="button" class="sprtadm-btn sprtadm-btn-outline" onclick="toggleEditMode()">
                                <i class="bi bi-pencil me-1"></i>
                                <span id="editButtonText">Editar</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <form method="POST" id="requestForm">
                        <div class="sprtadm-detail-card-body">
                            <?php if ($canEdit): ?>
                                <!-- Título -->
                                <div class="sprtadm-detail-field">
                                    <label class="sprtadm-detail-label">Título</label>
                                    <input type="text" name="title" class="sprtadm-form-control edit-field"
                                        value="<?= htmlspecialchars($request['TITLE']) ?>" style="display: none;" required>
                                    <div class="sprtadm-detail-value view-field">
                                        <?= htmlspecialchars($request['TITLE']) ?>
                                    </div>
                                </div>

                                <!-- Descrição -->
                                <div class="sprtadm-detail-field">
                                    <label class="sprtadm-detail-label">Descrição</label>
                                    <textarea name="description" class="sprtadm-form-control edit-field" rows="6"
                                        style="display: none;"
                                        required><?= htmlspecialchars($request['DESCRIPTION']) ?></textarea>
                                    <div class="sprtadm-detail-value view-field">
                                        <?= nl2br(htmlspecialchars($request['DESCRIPTION'])) ?>
                                    </div>
                                </div>

                                <!-- Passos para Reproduzir -->
                                <div class="sprtadm-detail-field">
                                    <label class="sprtadm-detail-label">Passos para Reproduzir</label>
                                    <textarea name="repro_steps" class="sprtadm-form-control edit-field" rows="4"
                                        style="display: none;"><?= htmlspecialchars($request['REPRO_STEPS'] ?? '') ?></textarea>
                                    <div class="sprtadm-detail-value view-field">
                                        <?= !empty($request['REPRO_STEPS']) ? nl2br(htmlspecialchars($request['REPRO_STEPS'])) : '<em class="text-muted">Não especificado</em>' ?>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="sprtadm-detail-field">
                                    <label class="sprtadm-detail-label">Status</label>
                                    <select name="status" class="sprtadm-form-select edit-field" style="display: none;">
                                        <option value="nao_resolvido" <?= $request['STATUS'] === 'nao_resolvido' ? 'selected' : '' ?>>Não Resolvido</option>
                                        <option value="em_processo" <?= $request['STATUS'] === 'em_processo' ? 'selected' : '' ?>>Em Processo</option>
                                        <option value="resolvido" <?= $request['STATUS'] === 'resolvido' ? 'selected' : '' ?>>
                                            Resolvido</option>
                                    </select>
                                    <div class="sprtadm-detail-value view-field">
                                        <span class="sprtadm-status-badge sprtadm-status-<?= $request['STATUS'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $request['STATUS'])) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Visualização apenas para ADM -->
                                <div class="sprtadm-detail-field">
                                    <label class="sprtadm-detail-label">Título</label>
                                    <div class="sprtadm-detail-value">
                                        <?= htmlspecialchars($request['TITLE']) ?>
                                    </div>
                                </div>

                                <div class="sprtadm-detail-field">
                                    <label class="sprtadm-detail-label">Descrição</label>
                                    <div class="sprtadm-detail-value">
                                        <?= nl2br(htmlspecialchars($request['DESCRIPTION'])) ?>
                                    </div>
                                </div>

                                <?php if (!empty($request['REPRO_STEPS'])): ?>
                                    <div class="sprtadm-detail-field">
                                        <label class="sprtadm-detail-label">Passos para Reproduzir</label>
                                        <div class="sprtadm-detail-value">
                                            <?= nl2br(htmlspecialchars($request['REPRO_STEPS'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="sprtadm-detail-field">
                                    <label class="sprtadm-detail-label">Status</label>
                                    <div class="sprtadm-detail-value">
                                        <span class="sprtadm-status-badge sprtadm-status-<?= $request['STATUS'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $request['STATUS'])) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Outros Detalhes (não editáveis) -->
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <div class="sprtadm-detail-field">
                                        <label class="sprtadm-detail-label">Tipo de Erro</label>
                                        <div class="sprtadm-detail-value">
                                            <?= formatErrorType($request['ERROR_TYPE']) ?>
                                            <?php if ($request['ERROR_TYPE'] === 'Other' && !empty($request['OTHER_ERROR_TYPE'])): ?>
                                                <br><small class="text-muted">Especificação:
                                                    <?= htmlspecialchars($request['OTHER_ERROR_TYPE']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="sprtadm-detail-field">
                                        <label class="sprtadm-detail-label">Frequência</label>
                                        <div class="sprtadm-detail-value">
                                            <?= ucfirst(str_replace('_', ' ', $request['FREQUENCY'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="sprtadm-detail-field">
                                        <label class="sprtadm-detail-label">Data de Reporte</label>
                                        <div class="sprtadm-detail-value">
                                            <?= date('d/m/Y H:i:s', strtotime($request['REPORTED_AT'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="sprtadm-detail-field">
                                        <label class="sprtadm-detail-label">Contexto</label>
                                        <div class="sprtadm-detail-value">
                                            <span class="sprtadm-badge sprtadm-badge-<?= $request['ERROR_CONTEXT'] ?>">
                                                <?= ucfirst($request['ERROR_CONTEXT']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Arquivo anexado -->
                            <?php if (!empty($request['FILE_PATH'])): ?>
                                <div class="sprtadm-detail-field">
                                    <label class="sprtadm-detail-label">Arquivo Anexado</label>
                                    <div class="sprtadm-detail-value">
                                        <a href="<?= htmlspecialchars($request['FILE_PATH']) ?>" target="_blank" download
                                            class="sprtadm-file-link">
                                            <i class="bi bi-download me-2"></i>
                                            Baixar Arquivo
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Método de Contato -->
                            <?php if (!empty($request['contact_method'])): ?>
                                <div class="sprtadm-detail-field">
                                    <label class="sprtadm-detail-label">Método de Contato Preferido</label>
                                    <div class="sprtadm-detail-value">
                                        <?= htmlspecialchars($request['contact_method']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($canEdit): ?>
                            <div class="sprtadm-detail-card-footer edit-actions" style="display: none;">
                                <button type="submit" class="sprtadm-btn sprtadm-btn-primary">
                                    <i class="bi bi-check me-1"></i>
                                    Salvar Alterações
                                </button>
                                <button type="button" class="sprtadm-btn sprtadm-btn-secondary" onclick="cancelEdit()">
                                    <i class="bi bi-x me-1"></i>
                                    Cancelar
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Coluna Lateral - Dados do Utilizador -->
            <div class="col-xl-4 col-lg-5">
                <?php if ($request['USER_ID']): ?>
                    <div class="sprtadm-detail-card">
                        <div class="sprtadm-detail-card-header">
                            <h3 class="sprtadm-detail-card-title">
                                <i class="bi bi-person me-2"></i>
                                Dados do Utilizador
                            </h3>
                        </div>
                        <div class="sprtadm-detail-card-body">
                            <!-- Avatar e Nome -->
                            <div class="sprtadm-user-profile">
                                <img src="../Imagens/avatares/<?= htmlspecialchars($request['KR_AVATAR'] ?: 'default.png') ?>"
                                    alt="Avatar" class="sprtadm-user-avatar">
                                <div class="sprtadm-user-info">
                                    <h4 class="sprtadm-user-name">
                                        <?= htmlspecialchars($request['KR_NAME'] ?: $request['KR_USER_NAME']) ?>
                                    </h4>
                                    <p class="sprtadm-user-username">@<?= htmlspecialchars($request['KR_USER_NAME']) ?></p>
                                </div>
                            </div>

                            <!-- Informações Básicas -->
                            <div class="sprtadm-user-details">
                                <div class="sprtadm-detail-field">
                                    <label class="sprtadm-detail-label">Email</label>
                                    <div class="sprtadm-detail-value">
                                        <?= htmlspecialchars($request['KR_EMAIL_USER']) ?>
                                    </div>
                                </div>

                                <?php if (!empty($request['KR_BIO'])): ?>
                                    <div class="sprtadm-detail-field">
                                        <label class="sprtadm-detail-label">Bio</label>
                                        <div class="sprtadm-detail-value">
                                            <?= nl2br(htmlspecialchars($request['KR_BIO'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="row g-2">
                                    <?php if (!empty($request['KR_COUNTRY'])): ?>
                                        <div class="col-12">
                                            <div class="sprtadm-detail-field">
                                                <label class="sprtadm-detail-label">País</label>
                                                <div class="sprtadm-detail-value">
                                                    <?= htmlspecialchars($request['KR_COUNTRY']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($request['KR_CITY'])): ?>
                                        <div class="col-12">
                                            <div class="sprtadm-detail-field">
                                                <label class="sprtadm-detail-label">Cidade</label>
                                                <div class="sprtadm-detail-value">
                                                    <?= htmlspecialchars($request['KR_CITY']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($request['KR_DATE_OF_BIRTH'])): ?>
                                        <div class="col-12">
                                            <div class="sprtadm-detail-field">
                                                <label class="sprtadm-detail-label">Data de Nascimento</label>
                                                <div class="sprtadm-detail-value">
                                                    <?= date('d/m/Y', strtotime($request['KR_DATE_OF_BIRTH'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="col-12">
                                        <div class="sprtadm-detail-field">
                                            <label class="sprtadm-detail-label">Género</label>
                                            <div class="sprtadm-detail-value">
                                                <?= htmlspecialchars($request['KR_GENERO']) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($request['ROBLOX_USERNAME'])): ?>
                                        <div class="col-12">
                                            <div class="sprtadm-detail-field">
                                                <label class="sprtadm-detail-label">Roblox Username</label>
                                                <div class="sprtadm-detail-value">
                                                    <?= htmlspecialchars($request['ROBLOX_USERNAME']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="col-12">
                                        <div class="sprtadm-detail-field">
                                            <label class="sprtadm-detail-label">Membro desde</label>
                                            <div class="sprtadm-detail-value">
                                                <?= date('d/m/Y', strtotime($request['KR_CREATED_AT'])) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($request['KR_LAST_LOGIN'])): ?>
                                        <div class="col-12">
                                            <div class="sprtadm-detail-field">
                                                <label class="sprtadm-detail-label">Último Login</label>
                                                <div class="sprtadm-detail-value">
                                                    <?= date('d/m/Y H:i', strtotime($request['KR_LAST_LOGIN'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Redes Sociais -->
                            <?php
                            $socials = [
                                'EMAIL_SOCIAL' => ['icon' => 'envelope', 'label' => 'Email Social'],
                                'WHATSAPP' => ['icon' => 'whatsapp', 'label' => 'WhatsApp'],
                                'YOUTUBE' => ['icon' => 'youtube', 'label' => 'YouTube'],
                                'INSTAGRAM' => ['icon' => 'instagram', 'label' => 'Instagram'],
                                'TIKTOK' => ['icon' => 'tiktok', 'label' => 'TikTok'],
                                'FACEBOOK' => ['icon' => 'facebook', 'label' => 'Facebook'],
                                'LINKEDIN' => ['icon' => 'linkedin', 'label' => 'LinkedIn'],
                                'GITHUB' => ['icon' => 'github', 'label' => 'GitHub'],
                                'TWITTER' => ['icon' => 'twitter', 'label' => 'Twitter']
                            ];

                            $hasSocials = false;
                            foreach ($socials as $key => $social) {
                                if (!empty($request[$key])) {
                                    $hasSocials = true;
                                    break;
                                }
                            }
                            ?>

                            <?php if ($hasSocials): ?>
                                <div class="sprtadm-user-socials">
                                    <h5 class="sprtadm-socials-title">Redes Sociais</h5>
                                    <div class="sprtadm-socials-list">
                                        <?php foreach ($socials as $key => $social): ?>
                                            <?php if (!empty($request[$key])): ?>
                                                <div class="sprtadm-social-item">
                                                    <i class="bi bi-<?= $social['icon'] ?> me-2"></i>
                                                    <span class="sprtadm-social-label"><?= $social['label'] ?>:</span>
                                                    <span class="sprtadm-social-value"><?= htmlspecialchars($request[$key]) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="sprtadm-detail-card">
                        <div class="sprtadm-detail-card-body text-center">
                            <i class="bi bi-person-x" style="font-size: 3rem; color: var(--color-gray-500);"></i>
                            <h4 class="mt-3">Utilizador Anónimo</h4>
                            <p class="text-muted">Este pedido foi submetido sem registo de utilizador.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($canEdit): ?>
        <script>
            let isEditMode = false;

            function toggleEditMode() {
                isEditMode = !isEditMode;

                const viewFields = document.querySelectorAll('.view-field');
                const editFields = document.querySelectorAll('.edit-field');
                const editActions = document.querySelector('.edit-actions');
                const editButton = document.getElementById('editButtonText');

                if (isEditMode) {
                    viewFields.forEach(field => field.style.display = 'none');
                    editFields.forEach(field => field.style.display = 'block');
                    editActions.style.display = 'block';
                    editButton.textContent = 'Cancelar';
                } else {
                    viewFields.forEach(field => field.style.display = 'block');
                    editFields.forEach(field => field.style.display = 'none');
                    editActions.style.display = 'none';
                    editButton.textContent = 'Editar';
                }
            }

            function cancelEdit() {
                // Reset form
                document.getElementById('requestForm').reset();
                toggleEditMode();
            }
        </script>
    <?php endif; ?>

    <style>
        .sprtadm-detail-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .sprtadm-detail-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--color-gray-800);
        }

        .sprtadm-detail-nav {
            margin-bottom: 1rem;
        }

        .sprtadm-detail-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-white);
            margin-bottom: 1rem;
        }

        .sprtadm-detail-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .sprtadm-status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sprtadm-detail-card {
            background-color: var(--color-gray-900);
            border-radius: var(--adm-border-radius);
            border: 1px solid var(--color-gray-800);
            margin-bottom: 2rem;
        }

        .sprtadm-detail-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--color-gray-800);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sprtadm-detail-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--color-white);
            margin: 0;
        }

        .sprtadm-detail-card-body {
            padding: 1.5rem;
        }

        .sprtadm-detail-card-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--color-gray-800);
            display: flex;
            gap: 1rem;
        }

        .sprtadm-detail-field {
            margin-bottom: 1.5rem;
        }

        .sprtadm-detail-label {
            display: block;
            font-weight: 600;
            color: var(--color-yellow);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .sprtadm-detail-value {
            color: var(--color-white);
            line-height: 1.6;
        }

        .sprtadm-file-link {
            color: var(--color-yellow);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: var(--color-gray-800);
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .sprtadm-file-link:hover {
            background-color: var(--color-gray-700);
            color: var(--color-yellow-hover);
        }

        .sprtadm-user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--color-gray-800);
        }

        .sprtadm-user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--color-yellow);
        }

        .sprtadm-user-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--color-white);
            margin: 0;
        }

        .sprtadm-user-username {
            color: var(--color-gray-400);
            margin: 0;
            font-size: 0.9rem;
        }

        .sprtadm-socials-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--color-yellow);
            margin-bottom: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--color-gray-800);
        }

        .sprtadm-social-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .sprtadm-social-label {
            color: var(--color-gray-400);
            min-width: 80px;
        }

        .sprtadm-social-value {
            color: var(--color-white);
        }

        @media (max-width: 1200px) {
            .sprtadm-detail-container {
                max-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .sprtadm-detail-container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }

            .sprtadm-detail-title {
                font-size: 1.5rem;
            }

            .sprtadm-detail-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .sprtadm-user-profile {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</body>
</html>