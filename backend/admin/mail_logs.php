<?php
// backend/admin/mail_logs.php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

try {
    // Ensure table exists (id, recipient_type, recipient_email, subject, status, created_at)
    $conn->query("CREATE TABLE IF NOT EXISTS mail_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recipient_type ENUM('admin','customer') NOT NULL,
        recipient_email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        status VARCHAR(32) NOT NULL DEFAULT 'sent',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // List logs with optional filters
        $type   = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = max(1, min(100, (int)($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];
        $types = '';
        if ($type === 'admin' || $type === 'customer') {
            $where[] = 'recipient_type = ?';
            $params[] = $type;
            $types .= 's';
        }
        if ($search !== '') {
            $where[] = '(recipient_email LIKE CONCAT("%", ?, "%") OR subject LIKE CONCAT("%", ?, "%"))';
            $params[] = $search;
            $params[] = $search;
            $types .= 'ss';
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Count
        $sqlCnt = "SELECT COUNT(*) AS c FROM mail_logs $whereSql";
        if ($types) {
            $stmt = $conn->prepare($sqlCnt);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $conn->query($sqlCnt);
        }
        $row = $res->fetch_assoc();
        $total = (int)($row['c'] ?? 0);

        // Data
        $sqlData = "SELECT id, recipient_type, recipient_email, subject, status, created_at
                    FROM mail_logs $whereSql
                    ORDER BY created_at DESC, id DESC
                    LIMIT ? OFFSET ?";
        $stmt2 = $conn->prepare($sqlData);
        if ($types) {
            $types2 = $types . 'ii';
            $stmt2->bind_param($types2, ...array_merge($params, [$limit, $offset]));
        } else {
            $stmt2->bind_param('ii', $limit, $offset);
        }
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $items = [];
        while ($r = $res2->fetch_assoc()) { $items[] = $r; }

        echo json_encode(['ok' => true, 'total' => $total, 'items' => $items]);
        exit;
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) { $data = []; }
        $action = strtolower(trim($data['action'] ?? ''));
        if ($action === 'delete') {
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid id']); exit; }
            $stmt = $conn->prepare('DELETE FROM mail_logs WHERE id = ?');
            $stmt->bind_param('i', $id);
            $ok = $stmt->execute();
            echo json_encode(['ok' => (bool)$ok]);
            exit;
        }
        if ($action === 'update') {
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid id']); exit; }
            $subject = trim((string)($data['subject'] ?? ''));
            $status  = trim((string)($data['status'] ?? ''));
            if ($subject === '' && $status === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'No fields to update']); exit; }
            // Build dynamic update for provided fields
            $fields = [];
            $params = [];
            $types  = '';
            if ($subject !== '') { $fields[] = 'subject = ?'; $params[] = $subject; $types .= 's'; }
            if ($status !== '')  { $fields[] = 'status = ?';  $params[] = $status;  $types .= 's'; }
            $params[] = $id; $types .= 'i';
            $sql = 'UPDATE mail_logs SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $ok = $stmt->execute();
            echo json_encode(['ok' => (bool)$ok]);
            exit;
        }
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'Unknown action']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
