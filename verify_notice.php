<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/ui.php';

$user = require_auth();
if (is_email_verified($user)) {
    redirect('/kyc_status.php');
}

render_page_start('Email Verification Required', 'Please verify your email before proceeding.', [
    ['href' => '/logout.php', 'label' => 'Logout']
]);
?>
<div class="alert info">We sent a verification link to <strong><?=h($user['email'])?></strong>.</div>
<form method="post" action="<?=h(app_path('/resend_verification.php'))?>" class="form-grid">
  <?=csrf_input()?>
  <button class="btn" type="submit">Resend verification email</button>
</form>
<?php render_page_end(); ?>
