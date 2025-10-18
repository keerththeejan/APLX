<?php
return function(mysqli $conn){
  $conn->query("CREATE TABLE IF NOT EXISTS customer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(32) DEFAULT '',
    address VARCHAR(255) DEFAULT '',
    district VARCHAR(100) DEFAULT '',
    province VARCHAR(100) DEFAULT '',
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_customer_email (email)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
};
