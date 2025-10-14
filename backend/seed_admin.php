<?php
// Run once from browser: http://localhost/APLX/Parcel/backend/seed_admin.php
require_once __DIR__ . '/init.php';
$email = 'admin@parcel.local';
$name = 'Administrator';
$pass = 'admin123';

// If an admin profile already exists for this email, skip
$stmt = $conn->prepare('SELECT admin_id FROM admin_profile WHERE email=?');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    echo 'Admin already exists in admin_profile';
    exit;
}

// Choose a fixed admin_id = 1 if free, otherwise pick next available
$adminId = 1;
try {
    $res = $conn->query('SELECT MAX(admin_id) AS max_id FROM admin_profile');
    if ($row = $res->fetch_assoc()) {
        $max = (int)($row['max_id'] ?? 0);
        $adminId = max(1, $max + 1);
    }
} catch (Throwable $e) { /* ignore, keep default 1 */ }

$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO admin_profile (admin_id, name, email, password_hash, phone) VALUES (?,?,?,?,"")');
$stmt->bind_param('isss', $adminId, $name, $email, $hash);
$stmt->execute();

echo 'Admin (admin_profile) created: ' . htmlspecialchars($email) . ' / password: ' . htmlspecialchars($pass) . ' with admin_id=' . (int)$adminId;
