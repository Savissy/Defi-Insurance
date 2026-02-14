<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../ui.php';
$admin = require_admin();

$rows = $pdo->query('SELECT k.*, u.email FROM kyc_submissions k JOIN users u ON u.id = k.user_id ORDER BY k.created_at DESC LIMIT 200')->fetchAll();

render_page_start('Admin Dashboard', 'Review KYC submissions and manage compliance.', [
    ['href' => '/admin/wallet_lookup.php', 'label' => 'Wallet Lookup'],
    ['href' => '/logout.php', 'label' => 'Logout']
]);
?>
<p class="subtitle">Signed in as <?=h($admin['email'])?></p>
<table>
<tr><th>ID</th><th>User Email</th><th>Name</th><th>Status</th><th>Document</th><th>Action</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?=h((string)$r['id'])?></td>
  <td><?=h($r['email'])?></td>
  <td><?=h($r['full_name'])?></td>
  <td><span class="badge <?=h($r['status'])?>"><?=h($r['status'])?></span></td>
  <td><a href="<?=h(app_path($r['id_document_path']))?>" target="_blank">Open</a></td>
  <td><a class="btn secondary" href="<?=h(app_path('/admin/kyc_review.php'))?>?id=<?=h((string)$r['id'])?>">Review</a></td>
</tr>
<?php endforeach; ?>
</table>
<?php render_page_end(); ?>
