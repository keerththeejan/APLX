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

// Toggle to disable all authentication and treat requests as logged-in
$AUTH_DISABLED = false;

// Company / Support details (used in emails and UI)
// You may later persist and load these from DB; for now they are constants here.
$COMPANY_NAME   = 'Parcel Transport';
// Outgoing sender for system emails
$SUPPORT_EMAIL  = 'saravanyaa1@gmail.com';
$SUPPORT_PHONE  = '+91-00000-00000';
$SUPPORT_ADDRESS= 'Chennai, TN';
$BOOKING_EMAIL  = 'booking@parcel.local';

?>
