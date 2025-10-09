<?php
// backend/gallery_list.php
require_once __DIR__ . '/init.php';
header('Content-Type: application/json');

$conn->query("CREATE TABLE IF NOT EXISTS gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image_url VARCHAR(512) NOT NULL,
  tag VARCHAR(60) DEFAULT NULL,
  day TINYINT UNSIGNED DEFAULT NULL,
  month VARCHAR(12) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$q = $conn->query('SELECT id, image_url, tag, day, month, sort_order FROM gallery ORDER BY sort_order, id');
$rows = [];
while ($row = $q->fetch_assoc()) { $rows[] = $row; }

echo json_encode(['items' => $rows]);
