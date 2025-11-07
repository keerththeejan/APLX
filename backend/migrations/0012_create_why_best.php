<?php
return function(mysqli $conn){
  $conn->query("CREATE TABLE IF NOT EXISTS why_best (
    id TINYINT PRIMARY KEY,
    header_title VARCHAR(200) NOT NULL DEFAULT 'Why we are considered the best in business',
    header_subtext VARCHAR(400) NOT NULL DEFAULT 'Decentralized trade, direct transport, high flexibility and secure delivery.',
    center_image_url VARCHAR(600) NOT NULL DEFAULT '',
    f1_icon_text VARCHAR(16) NULL,
    f1_icon_url VARCHAR(512) NULL,
    f1_title VARCHAR(140) NOT NULL DEFAULT '',
    f1_desc VARCHAR(240) NOT NULL DEFAULT '',
    f2_icon_text VARCHAR(16) NULL,
    f2_icon_url VARCHAR(512) NULL,
    f2_title VARCHAR(140) NOT NULL DEFAULT '',
    f2_desc VARCHAR(240) NOT NULL DEFAULT '',
    f3_icon_text VARCHAR(16) NULL,
    f3_icon_url VARCHAR(512) NULL,
    f3_title VARCHAR(140) NOT NULL DEFAULT '',
    f3_desc VARCHAR(240) NOT NULL DEFAULT '',
    f4_icon_text VARCHAR(16) NULL,
    f4_icon_url VARCHAR(512) NULL,
    f4_title VARCHAR(140) NOT NULL DEFAULT '',
    f4_desc VARCHAR(240) NOT NULL DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $conn->query("INSERT IGNORE INTO why_best (id) VALUES (1)");
};
