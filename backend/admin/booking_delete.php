<?php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    // CSRF validation (uses helper from init.php if available)
    if (function_exists('csrf_check')) { csrf_check(); }

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid id']);
        exit;
    }

    // Bookings are stored in the shipments table in this app
    $stmt = $conn->prepare('DELETE FROM shipments WHERE id = ?');
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();

    echo json_encode(['success' => (bool)$ok]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
