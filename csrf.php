<?php
require_once __DIR__ . '/helpers.php';

function csrf_token(): string
{
    start_secure_session();
    $ttl = app_config()['security']['csrf_ttl'] ?? 7200;

    if (empty($_SESSION['_csrf']) || empty($_SESSION['_csrf_expires']) || $_SESSION['_csrf_expires'] < time()) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        $_SESSION['_csrf_expires'] = time() + $ttl;
    }

    return $_SESSION['_csrf'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf_or_die(): void
{
    start_secure_session();
    $token = $_POST['csrf_token'] ?? '';

    if (empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}
