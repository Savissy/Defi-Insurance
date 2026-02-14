<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ui.php';

start_secure_session();
$token = (string)($_GET['token'] ?? '');
$message = 'Invalid or expired token.';
$ok = false;

if ($token !== '') {
    $hash = hash('sha256', $token);

    try {
        $stmt = $pdo->prepare('SELECT * FROM email_verifications WHERE token_hash = :token_hash AND expires_at >= NOW() LIMIT 1');
        $stmt->execute(['token_hash' => $hash]);
        $row = $stmt->fetch();

        if ($row) {
            $pdo->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = :id')->execute(['id' => $row['user_id']]);
            $pdo->prepare('DELETE FROM email_verifications WHERE id = :id')->execute(['id' => $row['id']]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$row['user_id'];
            redirect('/kyc_status.php');
        }
    } catch (Throwable $e) {
        error_log('verify error: ' . $e->getMessage());
        $message = 'Unexpected error while verifying.';
    }
}

render_page_start('Email Verification');
?>
<div class="alert <?= $ok ? 'success' : 'error' ?>"><?=h($message)?></div>
<div class="actions"><a class="btn secondary" href="<?=h(app_path('/login.php'))?>">Go to login</a></div>
<?php render_page_end(); ?>
