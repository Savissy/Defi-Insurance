<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';

start_secure_session();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();

    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Use a valid email and password of at least 8 characters.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, created_at) VALUES (:email, :password_hash, :role, NOW())');
                $stmt->execute(['email' => $email, 'password_hash' => $passwordHash, 'role' => 'user']);
                $userId = (int)$pdo->lastInsertId();

                $token = random_token(32);
                $tokenHash = hash('sha256', $token);
                $ttl = app_config()['security']['verification_ttl'] ?? 3600;

                $pdo->prepare('INSERT INTO email_verifications (user_id, token_hash, expires_at, created_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL :ttl SECOND), NOW())')
                    ->execute(['user_id' => $userId, 'token_hash' => $tokenHash, 'ttl' => $ttl]);

                $verifyUrl = rtrim(app_config()['app']['url'], '/') . '/verify.php?token=' . urlencode($token);
                $sent = send_verification_email($email, $verifyUrl);
                $message = $sent
                    ? 'Registration successful. Check your email for verification link.'
                    : 'Registration complete, but email could not be sent. Configure SMTP and resend verification.';
            }
        } catch (Throwable $e) {
            error_log('register error: ' . $e->getMessage());
            $error = 'Unexpected error. Try again.';
        }
    }
}
?>
<!doctype html><html><body>
<h2>Register</h2>
<?php if ($message): ?><p style="color:green;"><?=h($message)?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red;"><?=h($error)?></p><?php endif; ?>
<form method="post">
  <?=csrf_input()?>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required minlength="8">
  <button type="submit">Create Account</button>
</form>
<p><a href="/login.php">Login</a></p>
</body></html>
