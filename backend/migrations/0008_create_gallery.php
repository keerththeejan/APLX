<?php
return function(mysqli $conn){
  $conn->query("CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL DEFAULT '',
    description TEXT,
    image_url VARCHAR(600) NOT NULL DEFAULT '',
    category VARCHAR(100) NOT NULL DEFAULT 'Other',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_active_order (is_active, sort_order, category)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
};
