<?php
// backend/seed_user.php
// Run once from browser: http://localhost/APLX/backend/seed_user.php
// Creates users table if missing and inserts a sample user account.

require_once __DIR__ . '/init.php';

// 1) Ensure users table exists (compatible with schema.sql)
$ddl = <<<SQL
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(32) NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;
$conn->query($ddl);

// 2) Desired seed user (allow overrides via query)
$name  = isset($_GET['name']) && $_GET['name'] !== '' ? trim($_GET['name']) : 'Sample User';
$email = isset($_GET['email']) && $_GET['email'] !== '' ? trim($_GET['email']) : 'user@parcel.local';
$pass  = isset($_GET['pass'])  && $_GET['pass']  !== '' ? (string)$_GET['pass'] : 'user123';
$role  = isset($_GET['role'])  && $_GET['role']  !== '' ? strtolower(trim($_GET['role'])) : 'customer';
if (!in_array($role, ['customer','admin'], true)) { $role = 'customer'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'Invalid email address';
    exit;
}

// 3) Skip if already exists
$exists = false;
$stmt = $conn->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    $exists = true;
}

if ($exists) {
    echo 'User already exists: ' . htmlspecialchars($email);
    exit;
}

// 4) Insert with hashed password
$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?,?,?,?, NOW())');
$stmt->bind_param('ssss', $name, $email, $hash, $role);
$stmt->execute();

echo 'User created: ' . htmlspecialchars($email) . ' / password: ' . htmlspecialchars($pass) . ' (role=' . htmlspecialchars($role) . ')';
