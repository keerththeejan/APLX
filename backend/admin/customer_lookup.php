<?php
// backend/admin/customer_lookup.php
// Lightweight lookup for customers by name/email for admin UI autocomplete
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
$limit = max(1, min(25, intval($_GET['limit'] ?? 10)));
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$items = [];
$total = 0;

if ($q !== '') {
    $like = '%' . $q . '%';
    // Only customers
    $stmt = $conn->prepare('SELECT COUNT(*) c FROM users WHERE role = "customer" AND (name LIKE ? OR email LIKE ?)');
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $total = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);

    $stmt = $conn->prepare('SELECT id, name, email FROM users WHERE role = "customer" AND (name LIKE ? OR email LIKE ?) ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->bind_param('ssii', $like, $like, $limit, $offset);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

echo json_encode([
    'total' => (int)$total,
    'items' => $items,
]);
