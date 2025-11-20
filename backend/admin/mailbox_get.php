<?php
// backend/admin/mailbox_get.php
// Returns full admin_mailbox item by id as JSON for detailed view in notifications modal
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

function respond($data, int $code = 200){ http_response_code($code); echo json_encode($data); exit; }

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) { respond(['ok'=>false, 'error'=>'Invalid id'], 400); }

    // Ensure table exists
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $conn->prepare('SELECT id, direction, from_email, to_email, subject, body, reply_to_id, created_at FROM admin_mailbox WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if (!$row) { respond(['ok'=>false, 'error'=>'Not found'], 404); }

    respond(['ok'=>true, 'item'=>$row]);
} catch (Throwable $e) {
    respond(['ok'=>false, 'error'=>'Server error'], 500);
}
