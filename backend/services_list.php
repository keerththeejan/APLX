<?php
// backend/services_list.php (public list)
require_once __DIR__ . '/init.php';
header('Content-Type: application/json');

// Ensure table exists (safe if exists)
$conn->query("CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image_url VARCHAR(512) NOT NULL,
  title VARCHAR(120) NOT NULL,
  description VARCHAR(500) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$q = $conn->query('SELECT id, image_url, title, description, sort_order FROM services ORDER BY sort_order, id');
$rows = [];
while ($row = $q->fetch_assoc()) { $rows[] = $row; }
echo json_encode(['items' => $rows]);
