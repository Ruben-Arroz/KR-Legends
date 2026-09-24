<?php
/**
 * suporte-update-status.php
 * Endpoint para atualizar o status de pedidos de suporte via AJAX
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

    // 3) Verificar autenticação e permissões
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['ADM', 'SUPERADM'])) {
        jsonResponse(false, 'Acesso negado: permissões insuficientes');
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
    if (!isset($data['request_id']) || !isset($data['new_status'])) {
        jsonResponse(false, 'Parâmetros obrigatórios em falta (request_id, new_status)');
    }

    $requestId = filter_var($data['request_id'], FILTER_VALIDATE_INT);
    $newStatus = trim($data['new_status']);

    // 7) Validar ID do pedido
    if ($requestId === false || $requestId <= 0) {
        jsonResponse(false, 'ID do pedido inválido');
    }

    // 8) Validar novo status
    $validStatuses = ['nao_resolvido', 'em_processo', 'resolvido'];
    if (!in_array($newStatus, $validStatuses)) {
        jsonResponse(false, 'Status inválido. Valores aceitos: ' . implode(', ', $validStatuses));
    }

    // 9) Verificar se o pedido existe e obter dados atuais
    $checkStmt = $mysqli->prepare("SELECT ID_REPORT, STATUS, TITLE FROM KR_SUPPORT_REQUESTS WHERE ID_REPORT = ?");
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

    $currentRequest = $result->fetch_assoc();
    $checkStmt->close();

    // 10) Verificar se o status realmente mudou
    if ($currentRequest['STATUS'] === $newStatus) {
        jsonResponse(true, 'Status já está atualizado', [
            'request_id' => $requestId,
            'status' => $newStatus,
            'title' => $currentRequest['TITLE']
        ]);
    }

    // 11) Iniciar transação
    $mysqli->autocommit(false);

    try {
        // 12) Atualizar o status
        $updateStmt = $mysqli->prepare("UPDATE KR_SUPPORT_REQUESTS SET STATUS = ? WHERE ID_REPORT = ?");
        if (!$updateStmt) {
            throw new Exception('Erro na preparação da atualização: ' . $mysqli->error);
        }

        $updateStmt->bind_param("si", $newStatus, $requestId);
        if (!$updateStmt->execute()) {
            throw new Exception('Erro ao atualizar status: ' . $updateStmt->error);
        }

        $affectedRows = $updateStmt->affected_rows;
        $updateStmt->close();

        // 13) Verificar se a atualização foi bem-sucedida
        if ($affectedRows === 0) {
            throw new Exception('Nenhuma alteração foi feita');
        }

        /* // 14) Log da ação (opcional - para auditoria)
        $logStmt = $mysqli->prepare("
            INSERT INTO KR_ADMIN_LOGS (user_id, action, details, created_at) 
            VALUES (?, 'support_status_update', ?, NOW())
        ");

        if ($logStmt) {
            $logDetails = json_encode([
                'request_id' => $requestId,
                'old_status' => $currentRequest['STATUS'],
                'new_status' => $newStatus,
                'admin_role' => $_SESSION['role'],
                'admin_user' => $_SESSION['user_name'],
                'request_title' => $currentRequest['TITLE']
            ]);

            $logStmt->bind_param("is", $_SESSION['id'], $logDetails);
            $logStmt->execute();
            $logStmt->close();
        } */

        // 15) Confirmar transação
        $mysqli->commit();

        // 16) Resposta de sucesso
        jsonResponse(true, 'Status atualizado com sucesso', [
            'request_id' => $requestId,
            'old_status' => $currentRequest['STATUS'],
            'new_status' => $newStatus,
            'title' => $currentRequest['TITLE'],
            'updated_by' => $_SESSION['user_name'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $mysqli->rollback();
        throw $e;
    }

} catch (Exception $e) {
    // Log do erro (em produção, usar sistema de logs apropriado)
    error_log("Erro em suporte-update-status.php: " . $e->getMessage() . " | Request ID: " . ($requestId ?? 'N/A') . " | User: " . ($_SESSION['user_name'] ?? 'N/A'));

    // Resposta de erro (não expor detalhes internos em produção)
    $isDevelopment = (ini_get('display_errors') == 1);
    $errorMessage = $isDevelopment ? $e->getMessage() : 'Erro interno do servidor';

    jsonResponse(false, $errorMessage);
} finally {
    // Restaurar autocommit
    if (isset($mysqli)) {
        $mysqli->autocommit(true);
    }
}