<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/functions.php';

// Check if user is logged in and has admin access
if (!is_logged_in() || !is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf']) || !verify_csrf_token($_POST['csrf'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Get and validate booking ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
    exit;
}

// Validate status
$allowedStatuses = ['pending', 'in_transit', 'delivered', 'cancelled'];
$status = isset($_POST['status']) ? $_POST['status'] : '';
if (!in_array($status, $allowedStatuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

try {
    // Update the booking status
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET status = :status, updated_at = NOW() 
        WHERE id = :id
    ");
    
    $result = $stmt->execute([
        ':status' => $status,
        ':id' => $id
    ]);
    
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Failed to update booking']);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
