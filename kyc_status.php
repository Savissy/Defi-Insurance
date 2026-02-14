<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ui.php';

$user = require_auth();

if (!is_email_verified($user)) {
    redirect('/verify_notice.php');
}

$stmt = $pdo->prepare('SELECT * FROM kyc_submissions WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
$stmt->execute(['user_id' => $user['id']]);
$kyc = $stmt->fetch();

if ($kyc && $kyc['status'] === 'approved') {
    redirect('/main.html');
}

render_page_start('KYC Status', 'Track your onboarding approval progress.', [
    ['href' => '/kyc.php', 'label' => 'Submit / Resubmit KYC'],
    ['href' => '/logout.php', 'label' => 'Logout']
]);

if (!$kyc) {
    echo '<div class="alert info">No KYC submission found yet.</div>';
} else {
    echo '<p>Status: <span class="badge ' . h($kyc['status']) . '">' . h($kyc['status']) . '</span></p>';
    if (!empty($kyc['admin_notes'])) {
        echo '<div class="alert info">Admin Notes: ' . h($kyc['admin_notes']) . '</div>';
    }
    if ($kyc['status'] === 'rejected') {
        echo '<div class="alert error">Your KYC was rejected. Please update details and resubmit.</div>';
    } else {
        echo '<div class="alert info">Your KYC is under review.</div>';
    }
}
render_page_end();
