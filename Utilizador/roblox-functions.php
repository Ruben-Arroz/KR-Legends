<?php
/**
 * -------------------------------------------------------------------------
 * roblox-functions.php
 * -------------------------------------------------------------------------
 * Funções para integração com a API pública do Roblox, incluindo cache
 * em ficheiro (70 segundos).
 *
 * Instruções:
 * 1. Cria uma pasta chamada "roblox_cache" no mesmo nível deste ficheiro.
 *    chmod 755 roblox_cache
 *    chown www-data:www-data roblox_cache  (ou o user/grupo do teu servidor)
 *
 * 2. Inclui este arquivo onde precisares:
 *     require_once __DIR__ . '/roblox-functions.php';
 *
 * 3. Chama: getRobloxUserData('UsernameRoblox');
 *    Se tiver cache válido (< 70s), devolve dados de roblox_cache/Username.json.
 *    Caso contrário, faz chamada à API, grava no cache e retorna.
 */


/**
 * Diretório onde serão guardados os JSONs de cache.
 */

define('ROBLOX_CACHE_DIR', __DIR__ . '/roblox_cache');
if (!is_dir(ROBLOX_CACHE_DIR)) {
    $oldmask = umask(0);
    mkdir(ROBLOX_CACHE_DIR, 0755, true);
    umask($oldmask);
}

/**
 * Tenta ler dados do cache em ficheiro para o username, se o ficheiro existir
 * e tiver sido modificado há menos de $ttlSegundos segundos.
 *
 * @param string $username
 * @param int    $ttlSegundos  Tempo de vida do cache em segundos (ex: 70).
 * @return array|null           Array associativo com os dados JSON ou null se não existir/expirado.
 */
function readRobloxCache(string $username, int $ttlSegundos = 70): ?array
{
    // Sanitiza o nome para evitar caracteres inválidos no nome do ficheiro
    $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $username);
    $cacheFile = ROBLOX_CACHE_DIR . "/{$sanitized}.json";

    if (!file_exists($cacheFile)) {
        return null;
    }

    $modified = @filemtime($cacheFile);
    if ($modified === false) {
        return null;
    }

    // Se o ficheiro foi modificado há mais de $ttlSegundos segundos, é expirado
    if ((time() - $modified) > $ttlSegundos) {
        @unlink($cacheFile);
        return null;
    }

    $content = @file_get_contents($cacheFile);
    if ($content === false) {
        return null;
    }

    $data = json_decode($content, true);
    return is_array($data) ? $data : null;
}


/**
 * Grava em disco o array de dados de um usuário Roblox, sob forma de JSON,
 * dentro de ROBLOX_CACHE_DIR/{username}.json
 *
 * @param string $username
 * @param array  $robloxData  Dados que queremos guardar (array associativo).
 * @return void
 */
function writeRobloxCache(string $username, array $robloxData): void
{
    $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $username);
    $cacheFile = ROBLOX_CACHE_DIR . "/{$sanitized}.json";

    // Se a pasta não existir, cria com permissões 0755
    if (!is_dir(ROBLOX_CACHE_DIR)) {
        @mkdir(ROBLOX_CACHE_DIR, 0755, true);
    }

    $json = json_encode($robloxData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json !== false) {
        @file_put_contents($cacheFile, $json);
        @chmod($cacheFile, 0644);
    }
}


/**
 * Valida um Roblox username usando a API pública de search.
 *
 * @param string $username
 * @return array  ['valid' => bool, 'data' => array|null, 'error' => string|null]
 */
function validateRobloxUsername(string $username): array
{
    if (trim($username) === '') {
        return ['valid' => false, 'data' => null, 'error' => 'Username é obrigatório'];
    }

    // Adiciona timestamp para evitar caches intermediários
    $timestamp = time();
    $url = "https://users.roblox.com/v1/users/search?keyword=" . urlencode($username) . "&limit=10&t=" . $timestamp;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true, // Mudado para true
        CURLOPT_TIMEOUT => 30,          // Aumentado timeout
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Cache-Control: no-cache'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log("Roblox API cURL Error: $curlError");
        return ['valid' => false, 'data' => null, 'error' => 'Falha na conexão com a API do Roblox: ' . $curlError];
    }

    if ($httpCode !== 200) {
        error_log("Roblox API HTTP Error: Código $httpCode para username '{$username}'");
        return ['valid' => false, 'data' => null, 'error' => 'Erro ao acessar a API do Roblox (HTTP ' . $httpCode . ')'];
    }

    $data = json_decode($response, true);
    if ($data === null) {
        error_log("JSON Parse Error: " . json_last_error_msg());
        return ['valid' => false, 'data' => null, 'error' => 'Erro ao processar resposta da API do Roblox'];
    }

    if (empty($data['data'])) {
        return ['valid' => false, 'data' => null, 'error' => 'Usuário não encontrado'];
    }

    // Procura por correspondência exata (case insensitive)
    foreach ($data['data'] as $user) {
        if (strcasecmp($user['name'], $username) === 0) {
            return ['valid' => true, 'data' => $user, 'error' => null];
        }
    }

    return ['valid' => false, 'data' => null, 'error' => 'Usuário não encontrado'];
}

/**
 * Busca dados detalhados de um usuário Roblox (incluindo avatar). Retorna null se não conseguir.
 * Usa cache em ficheiro de 70 segundos para evitar chamadas repetidas excessivas.
 *
 * @param string $username
 * @return array|null  [
 *   'id'          => int,
 *   'username'    => string,
 *   'displayName' => string,
 *   'description' => string,
 *   'created'     => string (ISO),
 *   'avatarUrl'   => string (URL),
 *   'isBanned'    => bool
 * ]
 */
function getRobloxUserData(string $username): ?array
{
    // 1) Tenta ler do cache em disco
    $cached = readRobloxCache($username, 70);
    if ($cached !== null) {
        return $cached;
    }

    // 2) Se não encontrou cache válido, valida o username
    $validation = validateRobloxUsername($username);
    if (!$validation['valid']) {
        error_log("Validação Roblox falhou para '{$username}': " . $validation['error']);
        return null;
    }

    $userId = $validation['data']['id'];
    $timestamp = time();

    // URLs com query string para evitar caches intermediários
    $userUrl = "https://users.roblox.com/v1/users/{$userId}?t={$timestamp}";
    $avatarUrl = "https://thumbnails.roblox.com/v1/users/avatar?userIds={$userId}&size=420x420&format=Png&t={$timestamp}";

    // Opções comuns de cURL
    $curlOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Cache-Control: no-cache'
        ]
    ];

    // 3) Buscar dados gerais
    $chUser = curl_init();
    curl_setopt_array($chUser, $curlOptions + [CURLOPT_URL => $userUrl]);
    $userResponse = curl_exec($chUser);
    $userHttpCode = curl_getinfo($chUser, CURLINFO_HTTP_CODE);
    curl_close($chUser);

    if ($userResponse === false || $userHttpCode !== 200) {
        error_log("Roblox API Error (user): HTTP $userHttpCode para userId $userId");
        return null;
    }
    $userData = json_decode($userResponse, true);
    if (!$userData || !isset($userData['name'])) {
        error_log("Falha ao parsear userData para userId $userId");
        return null;
    }

    // 4) Buscar avatar
    $chAvatar = curl_init();
    curl_setopt_array($chAvatar, $curlOptions + [CURLOPT_URL => $avatarUrl]);
    $avatarResponse = curl_exec($chAvatar);
    $avatarHttpCode = curl_getinfo($chAvatar, CURLINFO_HTTP_CODE);
    curl_close($chAvatar);

    if ($avatarResponse === false || $avatarHttpCode !== 200) {
        error_log("Roblox API Error (avatar): HTTP $avatarHttpCode para userId $userId");
        return null;
    }
    $avatarData = json_decode($avatarResponse, true);
    if (!$avatarData || !isset($avatarData['data'][0]['imageUrl'])) {
        error_log("Falha ao parsear avatarData para userId $userId");
        return null;
    }

    // 5) Monta o array final
    $result = [
        'id' => $userId,
        'username' => $userData['name'] ?? '',
        'displayName' => $userData['displayName'] ?? '',
        'description' => $userData['description'] ?? '',
        'created' => $userData['created'] ?? '',
        'avatarUrl' => $avatarData['data'][0]['imageUrl'] ?? null,
        'isBanned' => $userData['isBanned'] ?? false,
    ];

    // 6) Grava no cache em disco
    writeRobloxCache($username, $result);

    return $result;
}