<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

start_secure_session();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();

    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid credentials.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            redirect(post_login_redirect($user));
        }
    } catch (Throwable $e) {
        error_log('login error: ' . $e->getMessage());
        $error = 'Unexpected error. Try again.';
    }
}
?>
<!doctype html><html><body>
<h2>Login</h2>
<?php if ($error): ?><p style="color:red;"><?=h($error)?></p><?php endif; ?>
<form method="post">
  <?=csrf_input()?>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit">Login</button>
</form>
<p><a href="/register.php">Register</a></p>
</body></html>
