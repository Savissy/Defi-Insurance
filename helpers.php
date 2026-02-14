<?php

function app_config(): array
{
    static $config;
    if (!$config) {
        $config = require __DIR__ . '/config.php';
    }
    return $config;
}

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $config = app_config();
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_name($config['app']['session_name'] ?? 'sid');
    session_set_cookie_params([
        'lifetime' => $config['security']['session_lifetime'] ?? 7200,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function normalize_wallet_address(string $address): string
{
    return trim(mb_strtolower($address));
}

function random_token(int $bytes = 32): string
{
    return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
}

function send_verification_email(string $toEmail, string $verifyUrl): bool
{
    $config = app_config();

    $autoloadPath = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        error_log('PHPMailer missing. Run composer require phpmailer/phpmailer');
        return false;
    }

    require_once $autoloadPath;

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->Port = (int)$config['smtp']['port'];

        if (($config['smtp']['encryption'] ?? '') === 'tls') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (($config['smtp']['encryption'] ?? '') === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Verify your Insurance Finance account';
        $mail->Body = '<p>Click to verify your email:</p><p><a href="' . h($verifyUrl) . '">Verify Email</a></p>';
        $mail->AltBody = 'Verify your email: ' . $verifyUrl;

        return $mail->send();
    } catch (Throwable $e) {
        error_log('Verification mail error: ' . $e->getMessage());
        return false;
    }
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
