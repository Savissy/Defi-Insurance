<?php
return [
    'app' => [
        'name' => 'Insurance Finance',
        'url' => 'http://localhost/Defi-Insurance',
        'dev_bypass_email_verification' => false,
        'timezone' => 'UTC',
        'session_name' => 'insurance_finance_sid',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'insurance_finance',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'your_email@gmail.com',
        'password' => 'your_gmail_app_password',
        'encryption' => 'tls',
        'from_email' => 'no-reply@insurancefinance.local',
        'from_name' => 'Insurance Finance',
    ],
    'security' => [
        'session_lifetime' => 7200,
        'csrf_ttl' => 7200,
        'verification_ttl' => 3600,
        'wallet_challenge_ttl' => 300,
        'max_upload_bytes' => 5 * 1024 * 1024,
        'allowed_upload_mime' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ],
        'admin_email_allowlist' => [
            'admin@insurancefinance.local',
        ],
    ],
    'paths' => [
        'kyc_upload_dir' => __DIR__ . '/storage/kyc_uploads',
    ],
];
