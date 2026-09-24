<?php
// api/pwa_installed.php
// Endpoint: regista instalação / first_open / ping
header('Content-Type: application/json; charset=utf-8');
// Evitar cache de proxies / SW para respostas dinâmicas
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
$device_uuid = trim($input['device_uuid'] ?? '');
$reason = trim($input['reason'] ?? '');

// Validação do device_uuid — permite letras, números, '-' e '_' (8..64 chars)
if (!$device_uuid || !preg_match('/^[A-Za-z0-9\-_]{8,64}$/', $device_uuid)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'device_uuid inválido']);
    exit;
}

// include da ligação (usa o caminho que tens no projeto)
require_once __DIR__ . '/../../databaseconnect.php';

// Verificação básica da ligação
if (!isset($mysqli) || $mysqli->connect_errno) {
    http_response_code(500);
    error_log('[pwa_installed] DB connection missing or error: ' . ($mysqli->connect_error ?? 'no mysqli'));
    echo json_encode(['success' => false, 'error' => 'Falha na ligação à base de dados']);
    exit;
}

try {
    // Inserção ignorando duplicados -> conta apenas a primeira vez
    $insert_sql = "INSERT IGNORE INTO pwa_installs (device_uuid, installed_at, last_seen) VALUES (?, NOW(), NOW())";
    $stmt = $mysqli->prepare($insert_sql);
    if (!$stmt) {
        error_log('[pwa_installed] prepare failed: ' . $mysqli->error);
        throw new Exception('DB prepare failed');
    }

    $stmt->bind_param('s', $device_uuid);
    if (!$stmt->execute()) {
        error_log('[pwa_installed] execute failed: ' . $stmt->error);
        throw new Exception('DB execute failed');
    }

    $isNew = ($stmt->affected_rows === 1);

    // Atualiza last_seen sempre (mantém atividade recente)
    $stmt2 = $mysqli->prepare("UPDATE pwa_installs SET last_seen = NOW() WHERE device_uuid = ?");
    if ($stmt2) {
        $stmt2->bind_param('s', $device_uuid);
        $stmt2->execute();
    } else {
        error_log('[pwa_installed] update prepare failed: ' . $mysqli->error);
    }

    echo json_encode(['success' => true, 'new_install' => (bool)$isNew]);
} catch (Exception $e) {
    error_log('pwa_installed error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro no servidor']);
}