<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

$user = require_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/verify_notice.php');
}
verify_csrf_or_die();

if (is_email_verified($user)) {
    redirect('/kyc_status.php');
}

try {
    $token = random_token(32);
    $hash = hash('sha256', $token);
    $ttl = app_config()['security']['verification_ttl'] ?? 3600;

    $pdo->prepare('DELETE FROM email_verifications WHERE user_id = :user_id')->execute(['user_id' => $user['id']]);
    $pdo->prepare('INSERT INTO email_verifications (user_id, token_hash, expires_at, created_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL :ttl SECOND), NOW())')
        ->execute(['user_id' => $user['id'], 'token_hash' => $hash, 'ttl' => $ttl]);

    $verifyUrl = rtrim(app_config()['app']['url'], '/') . '/verify.php?token=' . urlencode($token);
    send_verification_email($user['email'], $verifyUrl);
} catch (Throwable $e) {
    error_log('resend verification error: ' . $e->getMessage());
}

redirect('/verify_notice.php');
