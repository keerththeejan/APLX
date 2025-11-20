<?php
// backend/init.php
require_once __DIR__ . '/config.php';
// Load Composer autoloader if available (for PHPMailer, etc.)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
require_once __DIR__ . '/lib/utils.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/migrations.php';
if (isset($conn) && $conn instanceof mysqli) { run_migrations($conn); }
require_once __DIR__ . '/lib/mailer.php';
?>
