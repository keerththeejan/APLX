<?php
// backend/seed_admin.php
// Run once from browser: http://localhost/APLX/backend/seed_admin.php
// Ensures users table exists and inserts a sample admin user.
require_once __DIR__ . '/init.php';

// 1) Create users table if missing
$conn->query("CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(32) NOT NULL DEFAULT 'customer',
  phone VARCHAR(32) NULL,
  address VARCHAR(255) NULL,
  status TINYINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 2) Desired sample admin
$name  = 'Administrator';
$email = 'admin@parcel.local';
$pass  = 'admin123';

// 3) Skip if already exists
$stmt = $conn->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    echo 'Admin already exists in users: ' . htmlspecialchars($email);
    exit;
}

// 4) Insert admin with hashed password
$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?,?,?,?, NOW())');
$role = 'admin';
$stmt->bind_param('ssss', $name, $email, $hash, $role);
$stmt->execute();

echo 'Admin (users) created: ' . htmlspecialchars($email) . ' / password: ' . htmlspecialchars($pass);
