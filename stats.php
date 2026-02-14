<?php
require_once __DIR__ . '/auth.php';
require_verified_and_approved();

$sql = "SELECT
SUM(CASE WHEN action_type='deposit_pool' THEN COALESCE(amount_lovelace,0) ELSE 0 END) AS total_pool_deposited,
SUM(CASE WHEN action_type='withdraw_shares' THEN COALESCE(amount_lovelace,0) ELSE 0 END) AS total_withdrawn,
SUM(CASE WHEN action_type='submit_claim' THEN 1 ELSE 0 END) AS total_claims_submitted,
SUM(CASE WHEN action_type='execute_claim' THEN 1 ELSE 0 END) AS total_claims_executed,
SUM(CASE WHEN action_type='mint_membership_sbt' THEN 1 ELSE 0 END) AS membership_minted,
AVG(CASE WHEN action_type='submit_claim' THEN amount_lovelace END) AS average_claim_amount
FROM insurance_transactions";
$row = $pdo->query($sql)->fetch() ?: [];
json_response(['ok' => true, 'stats' => $row]);
