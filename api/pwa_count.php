<?php
// api/pwa_count.php
// Devolve o número de instalações registadas
header('Content-Type: application/json; charset=utf-8');
// Evitar caches (proxies, SW, browser)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// include da ligação (usa o caminho que tens no projeto)
require_once __DIR__ . '/../../databaseconnect.php';

if (!isset($mysqli) || $mysqli->connect_errno) {
    http_response_code(500);
    error_log('pwa_count: DB connection missing or error: ' . ($mysqli->connect_error ?? 'no mysqli'));
    echo json_encode(['success' => false, 'error' => 'Falha na ligação à base de dados']);
    exit;
}

try {
    $sql = "SELECT COUNT(*) AS total FROM pwa_installs";
    $res = $mysqli->query($sql);
    if (!$res) {
        throw new Exception($mysqli->error);
    }
    $row = $res->fetch_assoc();
    $total = intval($row['total'] ?? 0);

    // Resposta final (sempre JSON consistente)
    echo json_encode(['success' => true, 'total' => $total]);
} catch (Exception $e) {
    error_log('pwa_count error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro no servidor']);
}