<?php
return function(mysqli $conn){
  $conn->query("CREATE TABLE IF NOT EXISTS hero_banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    eyebrow VARCHAR(150) NOT NULL DEFAULT 'Safe Transportation & Logistics',
    title VARCHAR(200) NOT NULL DEFAULT '',
    subtitle VARCHAR(200) NOT NULL DEFAULT '',
    tagline VARCHAR(300) NOT NULL DEFAULT '',
    cta1_text VARCHAR(80) NOT NULL DEFAULT 'Get Started',
    cta1_link VARCHAR(300) NOT NULL DEFAULT '/APLX/frontend/login.php',
    cta2_text VARCHAR(80) NOT NULL DEFAULT 'Learn More',
    cta2_link VARCHAR(300) NOT NULL DEFAULT '#',
    image_url VARCHAR(600) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_active_order (is_active, sort_order)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
};
