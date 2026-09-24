<?php
// status.php
header('Content-Type: application/json');
$path = __DIR__ . '/kr-status.json';

// Abrir/criar arquivo sem truncar (modo leitura/escrita binário)
$fp = fopen($path, 'c+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(["error" => "Não foi possível abrir o arquivo"]);
    exit;
}

// Lock exclusivo para evitar concorrência
if (flock($fp, LOCK_EX)) {
    fseek($fp, 0);
    $contents = stream_get_contents($fp);
    $data = $contents ? json_decode($contents, true) : [];

    if (!isset($data['places']) || !is_array($data['places'])) {
        $data['places'] = [];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $in  = json_decode(file_get_contents('php://input'), true);
        $pid = isset($in['placeId']) ? (string)$in['placeId'] : '';
        $jid = isset($in['jobId'])   ? (string)$in['jobId']   : '';

        if ($pid !== '' && $jid !== '') {
            // Inicializa estrutura base do lugar se necessário
            if (!isset($data['places'][$pid])) {
                $data['places'][$pid] = [
                    'instances'          => [],
                    'playersOnline'      => 0,
                    'timestamp'          => 0,
                    'version'            => '',
                    'nextMaintenance'    => isset($in['nextMaintenance']) ? $in['nextMaintenance'] : '',
                    'avgSessionDuration' => 0,
                    'sessions'           => [],
                    'lastSessionReset'   => date('Y-m-d'),
                ];
            }

            $place = &$data['places'][$pid];

            // Atualiza manutenção, se enviada
            if (isset($in['nextMaintenance'])) {
                $place['nextMaintenance'] = $in['nextMaintenance'];
            }

            // Atualização de heartbeat da instância
            if (isset($in['playersOnline'], $in['timestamp'])) {
                $place['instances'][$jid] = [
                    'playersOnline' => intval($in['playersOnline']),
                    'timestamp'     => intval($in['timestamp']),
                    'version'       => isset($in['version']) ? $in['version'] : ($place['instances'][$jid]['version'] ?? '')
                ];
            }

            // Se enviado tempo de sessão de jogador, regista-o
            if (isset($in['sessionDuration'])) {
                $dur = intval($in['sessionDuration']);
                if ($dur > 0 && $dur <= 86400) { // até 24 horas
                    $today = date('Y-m-d');
                    if ($place['lastSessionReset'] !== $today) {
                        $place['sessions']         = [];
                        $place['lastSessionReset'] = $today;
                    }
                    $place['sessions'][] = $dur;
                    $total = array_sum($place['sessions']);
                    $count = count($place['sessions']);
                    $place['avgSessionDuration'] = round($total / max($count, 1));
                }
            }

            // Agrega dados de todas as instâncias vivas
            $sumPlayers = 0;
            $maxTs      = 0;
            $versions   = [];

            foreach ($place['instances'] as $jidKey => $inst) {
                $sumPlayers += $inst['playersOnline'];
                $maxTs       = max($maxTs, $inst['timestamp']);

                if (!empty($inst['version'])) {
                    $versions[] = ltrim($inst['version'], 'v');
                }

                // Limpa instâncias antigas (inativas há +10 min)
                if (time() - $inst['timestamp'] > 600) {
                    unset($place['instances'][$jidKey]);
                }
            }

            $place['playersOnline'] = $sumPlayers;
            $place['timestamp']     = $maxTs;

            // Escolhe versão mais recente
            if (!empty($versions)) {
                usort($versions, 'version_compare');
                $place['version'] = 'v' . end($versions);
            }
        }

        // Reescreve JSON
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        http_response_code(204); // sucesso sem conteúdo
        exit;
    }

    // GET – Devolve o JSON atual
    flock($fp, LOCK_UN);
    fclose($fp);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Erro ao aplicar lock
fclose($fp);
http_response_code(500);
echo json_encode(["error" => "Não foi possível travar o arquivo"]);