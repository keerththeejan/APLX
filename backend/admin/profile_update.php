<?php
// backend/admin/profile_update.php
// Update current admin's basic profile: name, email, optional password. Returns JSON.
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

try {
    $u = current_user();
    $uid = (int)($u['id'] ?? 0);
    if (!$uid) { throw new Exception('Not authenticated'); }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Valid name and email are required']);
        exit;
    }

    // Optional password check
    $hash = null;
    if ($password !== '') {
        if ($confirm !== '' && $password !== $confirm) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Password and confirm do not match']);
            exit;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
    }

    // Upsert into admin_profile table keyed by admin_id
    $stmt = $conn->prepare(
        'INSERT INTO admin_profile (admin_id, name, email, password_hash, phone)
         VALUES (?, ?, ?, NULLIF(?, ""), NULLIF(?, ""))
         ON DUPLICATE KEY UPDATE
           name=VALUES(name),
           email=VALUES(email),
           phone=VALUES(phone),
           password_hash=COALESCE(VALUES(password_hash), admin_profile.password_hash),
           updated_at=CURRENT_TIMESTAMP'
    );
    $hashParam = $hash ?? '';
    $stmt->bind_param('issss', $uid, $name, $email, $hashParam, $phone);
    $stmt->execute();

    echo json_encode(['ok' => true, 'item' => ['name' => $name, 'email' => $email]]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to update profile']);
}
