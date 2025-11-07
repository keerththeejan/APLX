<?php
return function(mysqli $conn){
  $conn->query("CREATE TABLE IF NOT EXISTS about_section (
    id TINYINT PRIMARY KEY,
    eyebrow VARCHAR(150) NOT NULL DEFAULT 'Safe Transportation & Logistics',
    title VARCHAR(250) NOT NULL DEFAULT '',
    subtext VARCHAR(400) NOT NULL DEFAULT '',
    image_url VARCHAR(600) NOT NULL DEFAULT '',
    feature1_icon_text VARCHAR(16) NULL,
    feature1_icon_url VARCHAR(512) NULL,
    feature1_title VARCHAR(140) NOT NULL DEFAULT '',
    feature1_desc VARCHAR(240) NOT NULL DEFAULT '',
    feature2_icon_text VARCHAR(16) NULL,
    feature2_icon_url VARCHAR(512) NULL,
    feature2_title VARCHAR(140) NOT NULL DEFAULT '',
    feature2_desc VARCHAR(240) NOT NULL DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  // seed row
  $conn->query("INSERT IGNORE INTO about_section (id, eyebrow, title, subtext) VALUES (1,'Safe Transportation & Logistics','Modern transport system & secure packaging','We combine real‑time visibility with secure handling to move your freight quickly and safely.')");
};
