<?php
return function(mysqli $conn){
  $conn->query("CREATE TABLE IF NOT EXISTS hero_settings (
    id TINYINT UNSIGNED NOT NULL DEFAULT 1 PRIMARY KEY,
    eyebrow VARCHAR(150) NOT NULL DEFAULT 'Safe Transportation & Logistics',
    title VARCHAR(200) NOT NULL DEFAULT '',
    subtitle VARCHAR(200) NOT NULL DEFAULT '',
    tagline VARCHAR(300) NOT NULL DEFAULT '',
    cta1_text VARCHAR(80) NOT NULL DEFAULT 'Get Started',
    cta1_link VARCHAR(300) NOT NULL DEFAULT '/APLX/frontend/login.php',
    cta2_text VARCHAR(80) NOT NULL DEFAULT 'Learn More',
    cta2_link VARCHAR(300) NOT NULL DEFAULT '#',
    background_url VARCHAR(600) NOT NULL DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
};
