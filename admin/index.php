<?php
require_once __DIR__ . '/../auth.php';
$admin = require_admin();

$rows = $pdo->query('SELECT k.*, u.email FROM kyc_submissions k JOIN users u ON u.id = k.user_id ORDER BY k.created_at DESC LIMIT 200')->fetchAll();
?>
<!doctype html><html><body>
<h2>Admin Dashboard</h2>
<p>Welcome, <?=h($admin['email'])?> | <a href="/logout.php">Logout</a> | <a href="/admin/wallet_lookup.php">Wallet Lookup</a></p>
<table border="1" cellpadding="6">
<tr><th>ID</th><th>User Email</th><th>Name</th><th>Status</th><th>Document</th><th>Actions</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?=h((string)$r['id'])?></td>
  <td><?=h($r['email'])?></td>
  <td><?=h($r['full_name'])?></td>
  <td><?=h($r['status'])?></td>
  <td><a href="/<?=h($r['id_document_path'])?>" target="_blank">View File</a></td>
  <td><a href="/admin/kyc_review.php?id=<?=h((string)$r['id'])?>">Review</a></td>
</tr>
<?php endforeach; ?>
</table>
</body></html>
