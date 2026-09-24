<?php
/**
 * suporte-delete.php
 * Endpoint para excluir pedidos de suporte via AJAX
 */

// Configurações de segurança
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();

// Headers para JSON
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Função para retornar resposta JSON
function jsonResponse($success, $message = '', $data = null)
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('c')
    ]);
    exit;
}

/**
 * Remove arquivo anexado do pedido de suporte
 * @param int $requestId - ID do pedido
 * @return array - Informações sobre arquivos removidos
 */
function removeAttachedFiles($requestId)
{
    $removedFiles = [];
    $reportsDir = __DIR__ . '/../Imagens/reports/';

    // Verificar se o diretório existe
    if (!is_dir($reportsDir)) {
        return $removedFiles;
    }

    // Buscar arquivos com o padrão report_[id].*
    $pattern = "report_{$requestId}.*";
    $files = glob($reportsDir . $pattern);

    foreach ($files as $file) {
        if (is_file($file)) {
            $filename = basename($file);
            if (unlink($file)) {
                $removedFiles[] = $filename;
            } else {
                error_log("Erro ao remover arquivo: " . $file);
            }
        }
    }

    return $removedFiles;
}

try {
    // 1) Verificar se é requisição AJAX
    if (
        !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
    ) {
        jsonResponse(false, 'Acesso negado: requisição inválida');
    }

    // 2) Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Método não permitido');
    }

    // 3) Verificar autenticação e permissões (apenas SUPERADM pode excluir)
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'SUPERADM') {
        jsonResponse(false, 'Acesso negado: apenas SUPERADM pode excluir pedidos');
    }

    // 4) Incluir conexão com base de dados
    require_once __DIR__ . '/../../databaseconnect.php';

    // 5) Obter dados JSON do corpo da requisição
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse(false, 'Dados JSON inválidos: ' . json_last_error_msg());
    }

    // 6) Validar dados recebidos
    if (!isset($data['request_id'])) {
        jsonResponse(false, 'Parâmetro obrigatório em falta: request_id');
    }

    $requestId = filter_var($data['request_id'], FILTER_VALIDATE_INT);

    // 7) Validar ID do pedido
    if ($requestId === false || $requestId <= 0) {
        jsonResponse(false, 'ID do pedido inválido');
    }

    // 8) Verificar se o pedido existe e obter dados
    $checkStmt = $mysqli->prepare("SELECT ID_REPORT, TITLE, FILE_PATH, USER_ID FROM KR_SUPPORT_REQUESTS WHERE ID_REPORT = ?");
    if (!$checkStmt) {
        jsonResponse(false, 'Erro na preparação da consulta: ' . $mysqli->error);
    }

    $checkStmt->bind_param("i", $requestId);
    if (!$checkStmt->execute()) {
        $checkStmt->close();
        jsonResponse(false, 'Erro ao executar consulta: ' . $checkStmt->error);
    }

    $result = $checkStmt->get_result();
    if ($result->num_rows === 0) {
        $checkStmt->close();
        jsonResponse(false, 'Pedido de suporte não encontrado');
    }

    $request = $result->fetch_assoc();
    $checkStmt->close();

    // 9) Iniciar transação
    $mysqli->autocommit(false);

    try {
        // 10) Remover arquivos anexados
        $removedFiles = removeAttachedFiles($requestId);

        // 11) Excluir o pedido da base de dados
        $deleteStmt = $mysqli->prepare("DELETE FROM KR_SUPPORT_REQUESTS WHERE ID_REPORT = ?");
        if (!$deleteStmt) {
            throw new Exception('Erro na preparação da exclusão: ' . $mysqli->error);
        }

        $deleteStmt->bind_param("i", $requestId);
        if (!$deleteStmt->execute()) {
            throw new Exception('Erro ao excluir pedido: ' . $deleteStmt->error);
        }

        $affectedRows = $deleteStmt->affected_rows;
        $deleteStmt->close();

        // 12) Verificar se a exclusão foi bem-sucedida
        if ($affectedRows === 0) {
            throw new Exception('Nenhum pedido foi excluído');
        }

        /* // 13) Log da ação (para auditoria)
        $logStmt = $mysqli->prepare("
            INSERT INTO KR_ADMIN_LOGS (user_id, action, details, created_at) 
            VALUES (?, 'support_request_delete', ?, NOW())
        ");

        if ($logStmt) {
            $logDetails = json_encode([
                'request_id' => $requestId,
                'request_title' => $request['TITLE'],
                'request_user_id' => $request['USER_ID'],
                'had_file' => !empty($request['FILE_PATH']),
                'removed_files' => $removedFiles,
                'admin_role' => $_SESSION['role'],
                'admin_user' => $_SESSION['user_name']
            ]);

            $logStmt->bind_param("is", $_SESSION['id'], $logDetails);
            $logStmt->execute();
            $logStmt->close();
        }
 */
        // 14) Confirmar transação
        $mysqli->commit();

        // 15) Resposta de sucesso
        jsonResponse(true, 'Pedido excluído com sucesso', [
            'request_id' => $requestId,
            'title' => $request['TITLE'],
            'removed_files' => $removedFiles,
            'deleted_by' => $_SESSION['user_name'],
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $mysqli->rollback();
        throw $e;
    }

} catch (Exception $e) {
    // Log do erro
    error_log("Erro em suporte-delete.php: " . $e->getMessage() . " | Request ID: " . ($requestId ?? 'N/A') . " | User: " . ($_SESSION['user_name'] ?? 'N/A'));

    // Resposta de erro
    $isDevelopment = (ini_get('display_errors') == 1);
    $errorMessage = $isDevelopment ? $e->getMessage() : 'Erro interno do servidor';

    jsonResponse(false, $errorMessage);
} finally {
    // Restaurar autocommit
    if (isset($mysqli)) {
        $mysqli->autocommit(true);
    }
}
?>