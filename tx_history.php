<?php
require_once __DIR__ . '/auth.php';

$user = require_verified_and_approved();
$address = normalize_wallet_address((string)($_GET['address'] ?? ''));
if ($address === '') {
    json_response(['ok' => false, 'error' => 'address is required'], 422);
}

$stmt = $pdo->prepare('SELECT id, tx_hash, action_type, reference_id, actor_wallet_address, counterparty_wallet_address, amount_lovelace, asset_unit, status, created_at FROM insurance_transactions WHERE actor_wallet_address = :address OR counterparty_wallet_address = :address ORDER BY id DESC LIMIT 200');
$stmt->execute(['address' => $address]);
$transactions = $stmt->fetchAll();

$response = ['ok' => true, 'transactions' => $transactions];

if (is_admin($user)) {
    $hash = hash('sha256', $address);
    $u = $pdo->prepare('SELECT email, wallet_address, wallet_verified_at FROM users WHERE wallet_address_hash = :hash LIMIT 1');
    $u->execute(['hash' => $hash]);
    $response['bound_user'] = $u->fetch() ?: null;
}

json_response($response);
