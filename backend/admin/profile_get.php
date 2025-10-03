<?php
// backend/admin/profile_get.php
header('Content-Type: application/json');
require_once __DIR__ . '/../init.php';
require_admin();

try {
    $u = current_user();
    $uid = (int)($u['id'] ?? 0);
    if (!$uid) throw new Exception('No user');

    // Basics
    $stmt = $conn->prepare('SELECT name, email FROM users WHERE id=?');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $rowUser = $stmt->get_result()->fetch_assoc() ?: [];

    // Profile
    $stmt = $conn->prepare('SELECT phone, company, address, city, state, country, pincode FROM user_profiles WHERE user_id=?');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $rowProf = $stmt->get_result()->fetch_assoc() ?: [];

    echo json_encode([
        'ok' => true,
        'item' => array_merge([
            'name' => $rowUser['name'] ?? '',
            'email' => $rowUser['email'] ?? ''
        ], $rowProf)
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to load profile']);
}
