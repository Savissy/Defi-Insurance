<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';
require_once __DIR__ . '/../ui.php';

start_secure_session();
if (($u = current_user()) && is_admin($u)) {
    redirect('/admin/index.php');
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash']) || !is_admin($user)) {
        $error = 'Invalid admin credentials.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        redirect('/admin/index.php');
    }
}

render_page_start('Admin Login', 'Sign in with an allowlisted admin account.');
if ($error) echo '<div class="alert error">' . h($error) . '</div>';
?>
<form method="post" class="form-grid">
  <?=csrf_input()?>
  <label>Email</label><input type="email" name="email" required>
  <label>Password</label><input type="password" name="password" required>
  <button class="btn" type="submit">Login</button>
</form>
<?php render_page_end(); ?>
