<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/ui.php';

$user = require_auth();
if (!is_email_verified($user)) {
    redirect('/verify_notice.php');
}

$status = user_kyc_status((int)$user['id']);
if ($status === 'approved') {
    redirect('/main.html');
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();

    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $phone = trim((string)($_POST['phone_number'] ?? ''));
    $country = trim((string)($_POST['country'] ?? ''));
    $businessName = trim((string)($_POST['business_name'] ?? ''));

    if ($fullName === '' || $phone === '' || $country === '') {
        $error = 'Full name, phone number, and country are required.';
    } elseif (empty($_FILES['id_document']) || $_FILES['id_document']['error'] !== UPLOAD_ERR_OK) {
        $error = 'A valid ID/proof file is required.';
    } else {
        $config = app_config();
        $file = $_FILES['id_document'];
        $max = $config['security']['max_upload_bytes'];

        if ($file['size'] > $max) {
            $error = 'File too large. Max is 5MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';
            $allowed = $config['security']['allowed_upload_mime'];

            if (!in_array($mime, $allowed, true)) {
                $error = 'Only PDF/JPG/PNG files are allowed.';
            } else {
                $extMap = [
                    'application/pdf' => 'pdf',
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                ];
                $ext = $extMap[$mime] ?? 'bin';
                $randomName = bin2hex(random_bytes(16)) . '.' . $ext;
                $uploadDir = $config['paths']['kyc_upload_dir'];
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $dest = $uploadDir . '/' . $randomName;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $error = 'File upload failed.';
                } else {
                    try {
                        $pdo->prepare('INSERT INTO kyc_submissions (user_id, full_name, phone_number, country, business_name, id_document_path, id_document_mime, id_document_size, status, admin_notes, created_at, updated_at) VALUES (:user_id, :full_name, :phone_number, :country, :business_name, :id_document_path, :id_document_mime, :id_document_size, :status, :admin_notes, NOW(), NOW())')
                            ->execute([
                                'user_id' => $user['id'],
                                'full_name' => $fullName,
                                'phone_number' => $phone,
                                'country' => $country,
                                'business_name' => $businessName ?: null,
                                'id_document_path' => 'storage/kyc_uploads/' . $randomName,
                                'id_document_mime' => $mime,
                                'id_document_size' => $file['size'],
                                'status' => 'pending',
                                'admin_notes' => null,
                            ]);
                        $message = 'KYC submitted successfully.';
                    } catch (Throwable $e) {
                        error_log('kyc insert error: ' . $e->getMessage());
                        $error = 'Could not save KYC submission.';
                    }
                }
            }
        }
    }
}

render_page_start('KYC Submission', 'Complete onboarding to unlock dApp access.', [
    ['href' => '/kyc_status.php', 'label' => 'View Status'],
    ['href' => '/logout.php', 'label' => 'Logout']
]);
if ($message) echo '<div class="alert success">' . h($message) . '</div>';
if ($error) echo '<div class="alert error">' . h($error) . '</div>';
?>
<form method="post" enctype="multipart/form-data" class="form-grid">
  <?=csrf_input()?>
  <label>Full Name</label><input name="full_name" required>
  <label>Phone Number</label><input name="phone_number" required>
  <label>Country</label><input name="country" required>
  <label>Business Name (optional)</label><input name="business_name">
  <label>ID/Proof Document (PDF/JPG/PNG, max 5MB)</label>
  <input type="file" name="id_document" accept="application/pdf,image/png,image/jpeg" required>
  <button class="btn" type="submit">Submit KYC</button>
</form>
<?php render_page_end(); ?>
