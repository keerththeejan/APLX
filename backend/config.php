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
$SUPPORT_PHONE  = '+94 772912755';
$SUPPORT_ADDRESS= 'Visuvamadu, Mullaithivu';
$BOOKING_EMAIL  = 'admin@parcel.locall';

// SMTP configuration (set these for PHPMailer SMTP). For Gmail, use:
// host: smtp.gmail.com, port: 587 (TLS) or 465 (SSL)
// username: your full Gmail address, password: app password (not your actual login password)
$SMTP_HOST   = getenv('SMTP_HOST')   ?: '';
$SMTP_PORT   = getenv('SMTP_PORT')   ?: '';
$SMTP_USER   = getenv('SMTP_USER')   ?: '';
$SMTP_PASS   = getenv('SMTP_PASS')   ?: '';
$SMTP_SECURE = getenv('SMTP_SECURE') ?: 'tls'; // 'tls' or 'ssl'

?>
