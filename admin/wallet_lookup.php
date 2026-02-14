<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../ui.php';
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

render_page_start('Wallet Lookup', 'Find user account details by bound wallet.', [
    ['href' => '/admin/index.php', 'label' => 'Back to Dashboard']
]);
?>
<form method="get" class="form-grid">
  <label>Wallet Address</label>
  <input name="address" placeholder="addr1..." value="<?=h($address)?>">
  <button class="btn" type="submit">Lookup</button>
</form>

<?php if ($address !== ''): ?>
  <?php if ($result): ?>
    <h3>Result</h3>
    <pre class="mono"><?=h(json_encode($result, JSON_PRETTY_PRINT))?></pre>
  <?php else: ?>
    <div class="alert info">No bound user found for this wallet.</div>
  <?php endif; ?>
<?php endif; ?>
<?php render_page_end(); ?>
