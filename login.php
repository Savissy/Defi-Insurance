<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/ui.php';

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

render_page_start('Login', 'Access your Insurance Finance dashboard.');
if ($error) echo '<div class="alert error">' . h($error) . '</div>';
?>
<form method="post" class="form-grid">
  <?=csrf_input()?>
  <label>Email</label>
  <input type="email" name="email" placeholder="you@example.com" required>
  <label>Password</label>
  <input type="password" name="password" placeholder="••••••••" required>
  <div class="actions">
    <button class="btn" type="submit">Login</button>
    <a class="btn secondary" href="<?=h(app_path('/register.php'))?>">Create account</a>
  </div>
</form>
<?php render_page_end(); ?>
