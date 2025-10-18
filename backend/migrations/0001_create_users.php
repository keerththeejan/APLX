<?php
return function(mysqli $conn){
  $conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'customer',
    phone VARCHAR(32) NULL,
    address VARCHAR(255) NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_email (email)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
};
