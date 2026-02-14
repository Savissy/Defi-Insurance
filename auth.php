<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function current_user(): ?array
{
    start_secure_session();
    global $pdo;

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    return $stmt->fetch() ?: null;
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        redirect('/login.php');
    }
    return $user;
}

function is_email_verified(array $user): bool
{
    $config = app_config();
    if (!empty($config['app']['dev_bypass_email_verification'])) {
        return true;
    }
    return !empty($user['email_verified_at']);
}

function user_kyc_status(int $userId): ?string
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT status FROM kyc_submissions WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
    $stmt->execute(['user_id' => $userId]);
    $row = $stmt->fetch();
    return $row['status'] ?? null;
}

function require_verified_and_approved(): array
{
    $user = require_auth();
    if (!is_email_verified($user)) {
        redirect('/verify_notice.php');
    }

    $status = user_kyc_status((int)$user['id']);
    if ($status !== 'approved') {
        redirect('/kyc_status.php');
    }

    return $user;
}

function is_admin(array $user): bool
{
    $allowlist = app_config()['security']['admin_email_allowlist'] ?? [];
    return ($user['role'] ?? 'user') === 'admin' || in_array($user['email'], $allowlist, true);
}

function require_admin(): array
{
    $user = require_auth();
    if (!is_admin($user)) {
        http_response_code(403);
        exit('Forbidden');
    }
    return $user;
}

function post_login_redirect(array $user): string
{
    if (!is_email_verified($user)) {
        return '/verify_notice.php';
    }

    $status = user_kyc_status((int)$user['id']);
    if ($status !== 'approved') {
        return '/kyc_status.php';
    }

    return '/main.html';
}
