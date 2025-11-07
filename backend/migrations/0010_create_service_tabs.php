<?php
return function(mysqli $conn){
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
};
