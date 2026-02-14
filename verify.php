<?php
require_once __DIR__ . '/auth.php';

start_secure_session();
$token = (string)($_GET['token'] ?? '');
$message = 'Invalid or expired token.';

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
?>
<!doctype html><html><body>
<h2>Email Verification</h2>
<p><?=h($message)?></p>
<p><a href="/login.php">Go to login</a></p>
</body></html>
