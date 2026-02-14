<?php
require_once __DIR__ . '/auth.php';
require_verified_and_approved();

$limit = min(max((int)($_GET['limit'] ?? 20), 1), 100);
$stmt = $pdo->prepare('SELECT id, tx_hash, action_type, reference_id, actor_wallet_address, amount_lovelace, status, created_at FROM insurance_transactions ORDER BY id DESC LIMIT :lim');
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
json_response(['ok' => true, 'transactions' => $stmt->fetchAll()]);
