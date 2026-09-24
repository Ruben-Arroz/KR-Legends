<?php
// api/jwt_utils.php
function base64url_encode($data){
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function base64url_decode($data){
    $remainder = strlen($data) % 4;
    if ($remainder) $data .= str_repeat('=', 4 - $remainder);
    return base64_decode(strtr($data, '-_', '+/'));
}
function jwt_encode(array $payload, string $secret, int $exp_sec = 3600){
    $header = ['alg'=>'HS256','typ'=>'JWT'];
    $payload['iat'] = time();
    $payload['exp'] = time() + $exp_sec;
    $b64h = base64url_encode(json_encode($header));
    $b64p = base64url_encode(json_encode($payload));
    $sig = hash_hmac('sha256', "$b64h.$b64p", $secret, true);
    return "$b64h.$b64p." . base64url_encode($sig);
}
function jwt_decode(string $jwt, string $secret){
    $parts = explode('.', $jwt);
    if(count($parts) !== 3) return null;
    list($b64h, $b64p, $b64s) = $parts;
    $sig = base64url_decode($b64s);
    $valid = hash_hmac('sha256', "$b64h.$b64p", $secret, true);
    if(!hash_equals($valid, $sig)) return null;
    $payload = json_decode(base64url_decode($b64p), true);
    if(!$payload) return null;
    if(isset($payload['exp']) && time() > $payload['exp']) return null;
    return $payload;
}