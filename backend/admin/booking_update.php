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

    // CSRF validation via init.php helper
    if (function_exists('csrf_check')) { csrf_check(); }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    if ($id <= 0 || $status === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit;
    }

    // Optional: enforce allowed set
    $allowed = ['pending','in_transit','delivered','cancelled'];
    if (!in_array($status, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        exit;
    }

    $stmt = $conn->prepare('UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?');
    $stmt->bind_param('si', $status, $id);
    $ok = $stmt->execute();

    echo json_encode(['success' => (bool)$ok]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
