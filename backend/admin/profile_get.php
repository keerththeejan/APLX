<?php
// backend/admin/profile_get.php
header('Content-Type: application/json');
require_once __DIR__ . '/../init.php';
require_admin();

try {
    $u = current_user();
    $uid = (int)($u['id'] ?? 0);
    if (!$uid) throw new Exception('No user');

    // Load from admin_profile table (single row per admin_id)
    $stmt = $conn->prepare('SELECT name, email, phone FROM admin_profile WHERE admin_id=? LIMIT 1');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: [];

    echo json_encode([
        'ok' => true,
        'item' => [
            'name' => $row['name'] ?? '',
            'email' => $row['email'] ?? '',
            'phone' => $row['phone'] ?? ''
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to load profile']);
}
