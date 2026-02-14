<?php
require_once __DIR__ . '/auth.php';
$user = require_auth();

if (!is_email_verified($user)) {
    redirect('/verify_notice.php');
}

$stmt = $pdo->prepare('SELECT * FROM kyc_submissions WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
$stmt->execute(['user_id' => $user['id']]);
$kyc = $stmt->fetch();

if ($kyc && $kyc['status'] === 'approved') {
    redirect('/main.html');
}
?>
<!doctype html><html><body>
<h2>KYC Status</h2>
<?php if (!$kyc): ?>
  <p>No KYC submission found.</p>
  <p><a href="/kyc.php">Submit KYC</a></p>
<?php else: ?>
  <p>Status: <strong><?=h($kyc['status'])?></strong></p>
  <?php if (!empty($kyc['admin_notes'])): ?><p>Admin Notes: <?=h($kyc['admin_notes'])?></p><?php endif; ?>
  <?php if ($kyc['status'] === 'rejected'): ?>
    <p>Your KYC was rejected. Please resubmit.</p>
    <p><a href="/kyc.php">Resubmit KYC</a></p>
  <?php else: ?>
    <p>Your KYC is under review.</p>
  <?php endif; ?>
<?php endif; ?>
<p><a href="/logout.php">Logout</a></p>
</body></html>
