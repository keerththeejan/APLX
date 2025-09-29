<?php
// Run once from browser: http://localhost/Parcel/backend/seed_admin.php
require_once __DIR__ . '/init.php';
$email = 'admin@parcel.local';
$name = 'Administrator';
$pass = 'admin123';

$stmt = $conn->prepare('SELECT id FROM users WHERE email=?');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    echo 'Admin already exists';
    exit;
}
$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,"admin")');
$stmt->bind_param('sss', $name, $email, $hash);
$stmt->execute();

echo 'Admin created: ' . htmlspecialchars($email) . ' / password: ' . htmlspecialchars($pass);
