<?php
require_once __DIR__ . '/auth.php';

$user = require_verified_and_approved();
start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Invalid method'], 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    json_response(['ok' => false, 'error' => 'Invalid JSON'], 422);
}

$address = normalize_wallet_address((string)($payload['address'] ?? ''));
$signature = (string)($payload['signature'] ?? '');
$key = (string)($payload['key'] ?? '');
$signedChallenge = (string)($payload['challenge'] ?? '');

$challenge = $_SESSION['wallet_challenge'] ?? null;
if (!$challenge || $challenge['expires_at'] < time() || $challenge['user_id'] !== (int)$user['id']) {
    json_response(['ok' => false, 'error' => 'Challenge missing/expired'], 422);
}

if ($address === '' || $address !== $challenge['wallet_address']) {
    json_response(['ok' => false, 'error' => 'Address mismatch'], 422);
}

if ($signedChallenge !== base64_encode($challenge['challenge'])) {
    json_response(['ok' => false, 'error' => 'Challenge mismatch'], 422);
}

if ($signature === '' || $key === '') {
    json_response(['ok' => false, 'error' => 'Signature and key required'], 422);
}

$existing = normalize_wallet_address((string)($user['wallet_address'] ?? ''));
if ($existing !== '' && $existing !== $address) {
    json_response(['ok' => false, 'error' => 'Different wallet already bound'], 409);
}

try {
    $hash = hash('sha256', $address);
    $stmt = $pdo->prepare('UPDATE users SET wallet_address = :wallet_address, wallet_address_hash = :wallet_address_hash, wallet_verified_at = COALESCE(wallet_verified_at, NOW()) WHERE id = :id');
    $stmt->execute(['wallet_address' => $address, 'wallet_address_hash' => $hash, 'id' => $user['id']]);
    unset($_SESSION['wallet_challenge']);
    json_response(['ok' => true, 'bound' => true, 'first_bind' => $existing === '']);
} catch (Throwable $e) {
    error_log('wallet bind error: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Could not bind wallet'], 500);
}
