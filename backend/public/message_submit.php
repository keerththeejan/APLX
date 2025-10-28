<?php
// Public endpoint to accept quote/contact messages and store for admin notifications
require_once __DIR__ . '/../init.php';
header('Content-Type: application/json');

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
        exit;
    }

    // Normalize inputs (both forms supported)
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $delivery_city = trim($_POST['delivery_city'] ?? '');
    $freight_type  = trim($_POST['freight_type'] ?? '');
    $incoterms     = trim($_POST['incoterms'] ?? '');
    $fragile  = isset($_POST['fragile']) ? 1 : 0;
    $express  = isset($_POST['express']) ? 1 : 0;
    $insurance= isset($_POST['insurance']) ? 1 : 0;
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '') {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'Name and Email are required']);
        exit;
    }

    // Create table if not exists (idempotent)
    $conn->query("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(160) NOT NULL,
        phone VARCHAR(60) DEFAULT NULL,
        subject VARCHAR(200) DEFAULT NULL,
        service VARCHAR(120) DEFAULT NULL,
        delivery_city VARCHAR(120) DEFAULT NULL,
        freight_type VARCHAR(60) DEFAULT NULL,
        incoterms VARCHAR(60) DEFAULT NULL,
        fragile TINYINT(1) NOT NULL DEFAULT 0,
        express TINYINT(1) NOT NULL DEFAULT 0,
        insurance TINYINT(1) NOT NULL DEFAULT 0,
        body TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $conn->prepare('INSERT INTO messages (name, email, phone, subject, service, delivery_city, freight_type, incoterms, fragile, express, insurance, body) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->bind_param('ssssssssiiis', $name, $email, $phone, $subject, $service, $delivery_city, $freight_type, $incoterms, $fragile, $express, $insurance, $message);
    $ok = $stmt->execute();

    echo json_encode(['ok'=>(bool)$ok]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Server error']);
}
