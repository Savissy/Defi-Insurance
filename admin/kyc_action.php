<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';

require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/index.php');
}
verify_csrf_or_die();

$id = (int)($_POST['id'] ?? 0);
$action = ($_POST['action'] ?? '') === 'approved' ? 'approved' : 'rejected';
$notes = trim((string)($_POST['admin_notes'] ?? ''));

$stmt = $pdo->prepare('UPDATE kyc_submissions SET status = :status, admin_notes = :admin_notes, updated_at = NOW() WHERE id = :id');
$stmt->execute(['status' => $action, 'admin_notes' => ($notes ?: null), 'id' => $id]);

redirect('/admin/index.php');
