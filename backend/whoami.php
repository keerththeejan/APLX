<?php
// backend/whoami.php
header('Content-Type: application/json');
require_once __DIR__ . '/init.php';
try {
    $u = current_user();
    if (!$u) {
        echo json_encode(['ok' => true, 'loggedIn' => false, 'role' => null]);
        exit;
    }
    echo json_encode([
        'ok' => true,
        'loggedIn' => true,
        'role' => $u['role'] ?? null,
        'name' => $u['name'] ?? '',
        'email' => $u['email'] ?? ''
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false]);
}
