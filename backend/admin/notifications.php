<?php
// backend/admin/notifications.php
// JSON API for admin notifications, derived from recent shipment activity
header('Content-Type: application/json');
require_once __DIR__ . '/../init.php';
require_admin();

function respond($data, int $code = 200){ http_response_code($code); echo json_encode($data); exit; }

try {
    // Inputs
    $page   = max(1, intval($_GET['page'] ?? 1));
    $limitQ = $_GET['limit'] ?? '20';
    $all    = isset($_GET['all']) && $_GET['all'] !== '0';
    $limit  = $all ? 1000 : max(1, min(200, intval($limitQ)));
    $search = trim($_GET['search'] ?? '');

    // Build WHERE for search across common shipment fields
    $where = '';
    $params = [];
    $types = '';
    if ($search !== '') {
        $where = ' WHERE (tracking_number LIKE ? OR sender_name LIKE ? OR receiver_name LIKE ? OR origin LIKE ? OR destination LIKE ? OR status LIKE ?)';
        $like = '%' . $search . '%';
        $params = [$like,$like,$like,$like,$like,$like];
        $types = 'ssssss';
    }

    // Total count
    $sqlTotal = 'SELECT COUNT(*) AS c FROM shipments' . $where;
    $stmt = $conn->prepare($sqlTotal);
    if ($types) { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $total = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();

    // Fetch rows ordered by updated_at desc (fallback to created_at)
    $offset = ($page - 1) * $limit;
    if ($all) {
        $sql = 'SELECT id, tracking_number, sender_name, receiver_name, origin, destination, status, price, created_at, updated_at
                FROM shipments' . $where . ' ORDER BY COALESCE(updated_at, created_at) DESC';
        $stmt = $conn->prepare($sql);
        if ($types) { $stmt->bind_param($types, ...$params); }
    } else {
        $sql = 'SELECT id, tracking_number, sender_name, receiver_name, origin, destination, status, price, created_at, updated_at
                FROM shipments' . $where . ' ORDER BY COALESCE(updated_at, created_at) DESC LIMIT ? OFFSET ?';
        $stmt = $conn->prepare($sql);
        if ($types) {
            $types2 = $types . 'ii';
            $stmt->bind_param($types2, ...$params, $limit, $offset);
        } else {
            $stmt->bind_param('ii', $limit, $offset);
        }
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $items = [];
    while ($row = $res->fetch_assoc()) {
        $status = (string)($row['status'] ?? '');
        $title = 'Shipment Update';
        if ($status !== '') {
            $title = $status;
        }
        $msgParts = [];
        if (!empty($row['sender_name']) || !empty($row['receiver_name'])) {
            $from = trim((string)($row['sender_name'] ?? ''));
            $to   = trim((string)($row['receiver_name'] ?? ''));
            if ($from !== '' || $to !== '') {
                $msgParts[] = 'From ' . ($from !== '' ? $from : '—') . ' to ' . ($to !== '' ? $to : '—');
            }
        }
        if (!empty($row['origin']) || !empty($row['destination'])) {
            $o = trim((string)($row['origin'] ?? ''));
            $d = trim((string)($row['destination'] ?? ''));
            if ($o !== '' || $d !== '') {
                $msgParts[] = 'Route ' . ($o !== '' ? $o : '—') . ' → ' . ($d !== '' ? $d : '—');
            }
        }
        if (!empty($row['tracking_number'])) {
            $msgParts[] = 'AWB: ' . $row['tracking_number'];
        }
        $message = implode(' • ', $msgParts);
        $when = $row['updated_at'] ?? $row['created_at'] ?? null;
        $items[] = [
            'id' => (int)$row['id'],
            'type' => $status !== '' ? $status : 'Update',
            'title' => $title,
            'message' => $message,
            'created_at' => $when,
        ];
    }

    respond(['ok'=>true, 'total'=>$total, 'items'=>$items]);
} catch (Throwable $e) {
    respond(['ok'=>false, 'error'=>'Failed to load notifications'], 500);
}
