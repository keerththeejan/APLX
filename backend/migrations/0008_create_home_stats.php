<?php
return function(mysqli $conn){
  $conn->query("CREATE TABLE IF NOT EXISTS home_stats (
    id TINYINT UNSIGNED NOT NULL DEFAULT 1 PRIMARY KEY,
    hero_title VARCHAR(200) NOT NULL DEFAULT 'We Provide Full Assistance in Freight & Warehousing',
    hero_subtext VARCHAR(500) NOT NULL DEFAULT 'Comprehensive ocean, air, and land freight backed by modern warehousing. Track, optimize, and scale with confidence.',
    image_url VARCHAR(600) NOT NULL DEFAULT '',
    stat1_number VARCHAR(40) NOT NULL DEFAULT '35+',
    stat1_label VARCHAR(120) NOT NULL DEFAULT 'Countries Represented',
    stat2_number VARCHAR(40) NOT NULL DEFAULT '853+',
    stat2_label VARCHAR(120) NOT NULL DEFAULT 'Projects completed',
    stat3_number VARCHAR(40) NOT NULL DEFAULT '35+',
    stat3_label VARCHAR(120) NOT NULL DEFAULT 'Total Revenue',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
};
