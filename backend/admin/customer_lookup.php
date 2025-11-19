<?php
// backend/admin/customer_lookup.php
// Lightweight lookup for customers by name/email for admin UI autocomplete
require_once __DIR__ . '/../init.php';
require_admin();
require_once __DIR__ . '/customers_api.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
$limit = max(1, min(25, intval($_GET['limit'] ?? 10)));
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$items = [];
$total = 0;

if ($q !== '') {
    $like = '%' . $q . '%';
    $sqlCnt = "SELECT COUNT(*) c FROM `{$TABLE}` WHERE ({$COL_NAME} LIKE ? OR {$COL_EMAIL} LIKE ?)";
    $stmt = $conn->prepare($sqlCnt);
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $total = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);

    $sql = "SELECT {$COL_ID} id, {$COL_NAME} name, {$COL_EMAIL} email FROM `{$TABLE}` WHERE ({$COL_NAME} LIKE ? OR {$COL_EMAIL} LIKE ?) ORDER BY {$COL_ID} DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssii', $like, $like, $limit, $offset);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

echo json_encode([
    'total' => (int)$total,
    'items' => $items,
]);
