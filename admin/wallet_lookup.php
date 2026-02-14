<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$address = trim((string)($_GET['address'] ?? ''));
$result = null;
if ($address !== '') {
    $normalized = normalize_wallet_address($address);
    $hash = hash('sha256', $normalized);
    $stmt = $pdo->prepare('SELECT id, email, wallet_address, wallet_verified_at, created_at FROM users WHERE wallet_address_hash = :hash LIMIT 1');
    $stmt->execute(['hash' => $hash]);
    $result = $stmt->fetch();
}
?>
<!doctype html><html><body>
<h2>Wallet Lookup</h2>
<form method="get"><input name="address" placeholder="addr1..." value="<?=h($address)?>"><button>Lookup</button></form>
<?php if ($address !== ''): ?>
  <?php if ($result): ?>
    <pre><?=h(json_encode($result, JSON_PRETTY_PRINT))?></pre>
  <?php else: ?>
    <p>No bound user found for this wallet.</p>
  <?php endif; ?>
<?php endif; ?>
<p><a href="/admin/index.php">Back</a></p>
</body></html>
