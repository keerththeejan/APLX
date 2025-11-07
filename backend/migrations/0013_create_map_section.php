<?php
return function(mysqli $conn){
  $conn->query("CREATE TABLE IF NOT EXISTS map_section (
    id TINYINT PRIMARY KEY,
    header_title VARCHAR(150) NOT NULL DEFAULT 'Find Us Here',
    header_subtext VARCHAR(300) NOT NULL DEFAULT 'Visit our office location in Kilinochchi, Sri Lanka',
    map_embed_url VARCHAR(800) NOT NULL DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $conn->query("INSERT IGNORE INTO map_section (id) VALUES (1)");
};
