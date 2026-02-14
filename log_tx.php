<?php
require_once __DIR__ . '/auth.php';

require_verified_and_approved();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Invalid method'], 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    json_response(['ok' => false, 'error' => 'Invalid JSON'], 422);
}

$allowedActions = [
    'deposit_pool',
    'withdraw_shares',
    'submit_claim',
    'vote_claim',
    'execute_claim',
    'mint_membership_sbt',
    'premium_payment',
];

$actionType = (string)($payload['action_type'] ?? '');
if (!in_array($actionType, $allowedActions, true)) {
    json_response(['ok' => false, 'error' => 'Invalid action_type'], 422);
}

try {
    $stmt = $pdo->prepare('INSERT INTO insurance_transactions (tx_hash, action_type, reference_id, actor_wallet_address, counterparty_wallet_address, amount_lovelace, asset_unit, status, created_at) VALUES (:tx_hash, :action_type, :reference_id, :actor_wallet_address, :counterparty_wallet_address, :amount_lovelace, :asset_unit, :status, NOW())');
    $stmt->execute([
        'tx_hash' => trim((string)($payload['tx_hash'] ?? '')) ?: null,
        'action_type' => $actionType,
        'reference_id' => trim((string)($payload['reference_id'] ?? '')) ?: null,
        'actor_wallet_address' => normalize_wallet_address((string)($payload['actor_wallet_address'] ?? '')) ?: null,
        'counterparty_wallet_address' => normalize_wallet_address((string)($payload['counterparty_wallet_address'] ?? '')) ?: null,
        'amount_lovelace' => is_numeric($payload['amount_lovelace'] ?? null) ? (string)$payload['amount_lovelace'] : null,
        'asset_unit' => trim((string)($payload['asset_unit'] ?? '')) ?: null,
        'status' => trim((string)($payload['status'] ?? 'submitted')) ?: 'submitted',
    ]);
    json_response(['ok' => true]);
} catch (Throwable $e) {
    error_log('log_tx error: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Could not log transaction'], 500);
}
