<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';
require_once __DIR__ . '/../ui.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT k.*, u.email FROM kyc_submissions k JOIN users u ON u.id = k.user_id WHERE k.id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$kyc = $stmt->fetch();
if (!$kyc) {
    http_response_code(404);
    exit('KYC record not found.');
}

render_page_start('Review KYC #' . (string)$kyc['id'], 'Validate applicant details and choose an action.', [
    ['href' => '/admin/index.php', 'label' => 'Back to Dashboard']
]);
?>
<p><strong>User:</strong> <?=h($kyc['email'])?></p>
<p><strong>Name:</strong> <?=h($kyc['full_name'])?></p>
<p><strong>Phone:</strong> <?=h($kyc['phone_number'])?></p>
<p><strong>Country:</strong> <?=h($kyc['country'])?></p>
<p><strong>Business:</strong> <?=h($kyc['business_name'] ?? '-')?></p>
<p><strong>Status:</strong> <span class="badge <?=h($kyc['status'])?>"><?=h($kyc['status'])?></span></p>
<p><strong>Document:</strong> <a href="<?=h(app_path($kyc['id_document_path']))?>" target="_blank">Open Uploaded File</a></p>

<form method="post" action="<?=h(app_path('/admin/kyc_action.php'))?>" class="form-grid">
  <?=csrf_input()?>
  <input type="hidden" name="id" value="<?=h((string)$kyc['id'])?>">
  <label>Admin Notes</label>
  <textarea name="admin_notes" placeholder="Optional notes for user"></textarea>
  <div class="actions">
    <button class="btn success" type="submit" name="action" value="approved">Approve</button>
    <button class="btn danger" type="submit" name="action" value="rejected">Reject</button>
  </div>
</form>
<?php render_page_end(); ?>
