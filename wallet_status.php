<?php
require_once __DIR__ . '/auth.php';

$user = require_verified_and_approved();
$address = normalize_wallet_address((string)($_GET['address'] ?? ''));

$current = normalize_wallet_address((string)($user['wallet_address'] ?? ''));
$bound = $current !== '';

if ($address === '') {
    json_response([
        'ok' => true,
        'bound' => $bound,
        'wallet_address' => $bound ? $current : null,
        'can_use' => !$bound,
    ]);
}

json_response([
    'ok' => true,
    'bound' => $bound,
    'wallet_address' => $bound ? $current : null,
    'can_use' => !$bound || $current === $address,
]);
