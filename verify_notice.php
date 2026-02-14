<?php
require_once __DIR__ . '/auth.php';
$user = require_auth();
if (is_email_verified($user)) {
    redirect('/kyc_status.php');
}
?>
<!doctype html><html><body>
<h2>Verify your email</h2>
<p>We sent a verification link to <?=h($user['email'])?>.</p>
<form method="post" action="/resend_verification.php">
  <?php require_once __DIR__ . '/csrf.php'; echo csrf_input(); ?>
  <button type="submit">Resend verification email</button>
</form>
<p><a href="/logout.php">Logout</a></p>
</body></html>
