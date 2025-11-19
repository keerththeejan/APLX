<?php
// backend/admin/admin_mailbox.php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

function respond($data,$code=200){ http_response_code($code); echo json_encode($data); exit; }

try {
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

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        // Filters
        $direction = strtolower(trim((string)($_GET['direction'] ?? '')));
        $search    = trim((string)($_GET['search'] ?? ''));
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $limit     = max(1, min(100, (int)($_GET['limit'] ?? 10)));
        $offset    = ($page - 1) * $limit;

        $where = [];
        $types = '';
        $params = [];
        if ($direction === 'in' || $direction === 'out') {
            $where[] = 'direction = ?';
            $types  .= 's';
            $params[] = $direction;
        }
        if ($search !== '') {
            $where[] = '(from_email LIKE CONCAT("%", ?, "%") OR to_email LIKE CONCAT("%", ?, "%") OR subject LIKE CONCAT("%", ?, "%"))';
            $types  .= 'sss';
            $params[] = $search; $params[] = $search; $params[] = $search;
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Count
        $sqlCnt = "SELECT COUNT(*) AS c FROM admin_mailbox $whereSql";
        if ($types) { $stmt = $conn->prepare($sqlCnt); $stmt->bind_param($types, ...$params); $stmt->execute(); $res = $stmt->get_result(); }
        else { $res = $conn->query($sqlCnt); }
        $total = (int)($res->fetch_assoc()['c'] ?? 0);

        // Data
        $sql = "SELECT id, direction, from_email, to_email, subject, created_at FROM admin_mailbox $whereSql ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?";
        $stmt2 = $conn->prepare($sql);
        if ($types) {
            $types2 = $types . 'ii';
            $stmt2->bind_param($types2, ...array_merge($params, [$limit, $offset]));
        } else {
            $stmt2->bind_param('ii', $limit, $offset);
        }
        $stmt2->execute();
        $r2 = $stmt2->get_result();
        $items = [];
        while ($row = $r2->fetch_assoc()) { $items[] = $row; }
        respond(['ok'=>true,'total'=>$total,'items'=>$items,'page'=>$page,'limit'=>$limit]);
    }

    // Future: POST/DELETE for manual entries if needed
    respond(['error'=>'Method not allowed'], 405);
} catch (Throwable $e) {
    respond(['ok'=>false,'error'=>'Server error'], 500);
}
