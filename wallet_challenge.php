<?php
require_once __DIR__ . '/auth.php';

$user = require_verified_and_approved();
start_secure_session();

$address = normalize_wallet_address((string)($_GET['address'] ?? ''));
if ($address === '') {
    json_response(['ok' => false, 'error' => 'Wallet address required'], 422);
}

$challenge = sprintf(
    'Insurance Finance wallet bind\nUser:%d\nWallet:%s\nNonce:%s\nIssued:%s\nIP:%s',
    $user['id'],
    $address,
    bin2hex(random_bytes(16)),
    gmdate('c'),
    client_ip()
);

$_SESSION['wallet_challenge'] = [
    'user_id' => (int)$user['id'],
    'wallet_address' => $address,
    'challenge' => $challenge,
    'expires_at' => time() + (app_config()['security']['wallet_challenge_ttl'] ?? 300),
];

json_response(['ok' => true, 'challenge' => base64_encode($challenge)]);
