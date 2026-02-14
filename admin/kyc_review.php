<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT k.*, u.email FROM kyc_submissions k JOIN users u ON u.id = k.user_id WHERE k.id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$kyc = $stmt->fetch();
if (!$kyc) {
    exit('KYC record not found.');
}
?>
<!doctype html><html><body>
<h2>Review KYC #<?=h((string)$kyc['id'])?></h2>
<p>User: <?=h($kyc['email'])?></p>
<p>Name: <?=h($kyc['full_name'])?></p>
<p>Phone: <?=h($kyc['phone_number'])?></p>
<p>Country: <?=h($kyc['country'])?></p>
<p>Status: <?=h($kyc['status'])?></p>
<p>Document: <a href="/<?=h($kyc['id_document_path'])?>" target="_blank">Open</a></p>
<form method="post" action="/admin/kyc_action.php">
  <?=csrf_input()?>
  <input type="hidden" name="id" value="<?=h((string)$kyc['id'])?>">
  <textarea name="admin_notes" placeholder="Notes"></textarea>
  <button type="submit" name="action" value="approved">Approve</button>
  <button type="submit" name="action" value="rejected">Reject</button>
</form>
</body></html>
