<?php
return function(mysqli $conn){
  // Add icon_url column if it doesn't exist
  $res = $conn->query("SHOW COLUMNS FROM services LIKE 'icon_url'");
  if ($res && $res->num_rows === 0) {
    $conn->query("ALTER TABLE services ADD COLUMN icon_url VARCHAR(512) NULL AFTER icon");
  }
};
