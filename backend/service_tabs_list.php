<?php
// backend/service_tabs_list.php
require_once __DIR__ . '/init.php';
header('Content-Type: application/json');

try{
  // Ensure table exists (in case migrations not run)
  $conn->query("CREATE TABLE IF NOT EXISTS service_tabs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    icon_text VARCHAR(16) NULL,
    icon_url VARCHAR(512) NULL,
    image_url VARCHAR(600) NOT NULL DEFAULT '',
    bullets_json TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_active_order (is_active, sort_order)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

  $items = [];
  if ($q = $conn->query("SELECT id, title, icon_text, icon_url, image_url, bullets_json FROM service_tabs ORDER BY id ASC")){
    while ($row = $q->fetch_assoc()){
      $bul = [];
      if (!empty($row['bullets_json'])){
        $tmp = json_decode($row['bullets_json'], true);
        if (is_array($tmp)) { $bul = array_values(array_filter(array_map('strval',$tmp))); }
      }
      $items[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'] ?? '',
        'icon_text' => $row['icon_text'] ?? '',
        'icon_url' => $row['icon_url'] ?? '',
        'image_url' => $row['image_url'] ?? '',
        'bullets' => $bul,
      ];
    }
  }
  echo json_encode(['items' => $items]);
}catch(Throwable $e){
  http_response_code(200);
  echo json_encode(['items' => []]);
}
