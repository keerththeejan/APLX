<?php
return function(mysqli $conn){
  $conn->query("CREATE TABLE IF NOT EXISTS admin_mailbox (
    id INT AUTO_INCREMENT PRIMARY KEY,
    direction ENUM('in','out') NOT NULL,
    from_email VARCHAR(255) NOT NULL,
    to_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    reply_to_id INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dir_created (direction, created_at)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
};
