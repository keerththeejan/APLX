<?php
// backend/config.php
// Update DB credentials as per your MySQL setup
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '1234';
$DB_NAME = 'parcel_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    http_response_code(500);
    echo 'Database connection error. Please check backend/config.php settings.';
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
