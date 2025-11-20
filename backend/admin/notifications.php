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

    // Fetch shipments (limited) for activity feed
    $offset = ($page - 1) * $limit;
    $shipItems = [];
    {
        if ($all) {
            $sql = 'SELECT id, tracking_number, sender_name, receiver_name, origin, destination, status, price, created_at, updated_at
                    FROM shipments' . $where . ' ORDER BY COALESCE(updated_at, created_at) DESC LIMIT 200';
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
        while ($row = $res->fetch_assoc()) {
            $status = (string)($row['status'] ?? '');
            $title = $status !== '' ? $status : 'Shipment Update';
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
            if (!empty($row['tracking_number'])) { $msgParts[] = 'AWB: ' . $row['tracking_number']; }
            $shipItems[] = [
                'id' => (int)$row['id'],
                'type' => $status !== '' ? $status : 'Update',
                'title' => $title,
                'message' => implode(' • ', $msgParts),
                'created_at' => $row['updated_at'] ?? $row['created_at'] ?? null,
                'source' => 'shipment'
            ];
        }
    }

    // Fetch recent public messages
    $msgItems = [];
    {
        // Create table if not exists (defensive)
        $conn->query("CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(160) NOT NULL,
            phone VARCHAR(60) DEFAULT NULL,
            subject VARCHAR(200) DEFAULT NULL,
            service VARCHAR(120) DEFAULT NULL,
            delivery_city VARCHAR(120) DEFAULT NULL,
            freight_type VARCHAR(60) DEFAULT NULL,
            incoterms VARCHAR(60) DEFAULT NULL,
            fragile TINYINT(1) NOT NULL DEFAULT 0,
            express TINYINT(1) NOT NULL DEFAULT 0,
            insurance TINYINT(1) NOT NULL DEFAULT 0,
            body TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $lim = $all ? 200 : $limit;
        $off = $all ? 0 : $offset;
        $sqlM = 'SELECT id, name, email, phone, subject, service, delivery_city, created_at FROM messages ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $stmtM = $conn->prepare($sqlM);
        $stmtM->bind_param('ii', $lim, $off);
        $stmtM->execute();
        $resM = $stmtM->get_result();
        while ($m = $resM->fetch_assoc()) {
            $title = trim((string)($m['subject'] ?? ''));
            if ($title === '') $title = 'New Message';
            $msg = trim((string)($m['name'] ?? ''));
            if (!empty($m['service'])) $msg .= ($msg? ' • ' : '') . $m['service'];
            if (!empty($m['delivery_city'])) $msg .= ($msg? ' • ' : '') . $m['delivery_city'];
            $msgItems[] = [
                'id' => (int)$m['id'],
                'type' => 'Message',
                'title' => $title,
                'message' => $msg,
                'created_at' => $m['created_at'],
                'source' => 'message'
            ];
        }
    }

    // Fetch recent mail logs (outgoing via send_mail)
    $mailLogItems = [];
    {
        try {
            $conn->query("CREATE TABLE IF NOT EXISTS mail_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                recipient_type ENUM('admin','customer') NOT NULL,
                recipient_email VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                status VARCHAR(32) NOT NULL DEFAULT 'sent',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $lim = $all ? 200 : $limit;
            $off = $all ? 0 : $offset;
            $sqlL = 'SELECT id, recipient_type, recipient_email, subject, status, created_at FROM mail_logs ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?';
            $stmtL = $conn->prepare($sqlL);
            $stmtL->bind_param('ii', $lim, $off);
            $stmtL->execute();
            $resL = $stmtL->get_result();
            while ($r = $resL->fetch_assoc()) {
                $title = 'Mail: ' . ($r['subject'] ?? '');
                $msg = ($r['recipient_type'] ?? 'customer') . ' • ' . ($r['recipient_email'] ?? '') . ' • ' . ($r['status'] ?? '');
                $mailLogItems[] = [
                    'id' => (int)$r['id'],
                    'type' => 'Mail',
                    'title' => $title,
                    'message' => $msg,
                    'created_at' => $r['created_at'] ?? null,
                    'source' => 'mail_log'
                ];
            }
        } catch (Throwable $e) { /* ignore mail logs if not available */ }
    }

    // Fetch admin_mailbox (inbound/outbound threads)
    $mboxItems = [];
    {
        try {
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
            $lim = $all ? 200 : $limit;
            $off = $all ? 0 : $offset;
            $sqlB = 'SELECT id, direction, from_email, to_email, subject, body, created_at FROM admin_mailbox ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?';
            $stmtB = $conn->prepare($sqlB);
            $stmtB->bind_param('ii', $lim, $off);
            $stmtB->execute();
            $resB = $stmtB->get_result();
            while ($b = $resB->fetch_assoc()) {
                $dir = strtolower((string)($b['direction'] ?? ''));
                $title = ($dir === 'in' ? 'Incoming' : 'Outgoing') . ': ' . ($b['subject'] ?? '');
                $msg = ($b['from_email'] ?? '') . ' → ' . ($b['to_email'] ?? '');
                $preview = trim(mb_substr(strip_tags((string)($b['body'] ?? '')), 0, 160));
                $mboxItems[] = [
                    'id' => (int)$b['id'],
                    'type' => ($dir === 'in' ? 'Incoming Mail' : 'Outgoing Mail'),
                    'title' => $title,
                    'message' => $msg . ($preview ? (' • ' . $preview) : ''),
                    'created_at' => $b['created_at'] ?? null,
                    'source' => 'admin_mailbox'
                ];
            }
        } catch (Throwable $e) { /* ignore mailbox if not available */ }
    }

    // Merge and sort by time desc
    $allItems = array_merge($shipItems, $msgItems, $mailLogItems, $mboxItems);
    usort($allItems, function($a,$b){ return strcmp(($b['created_at']??''), ($a['created_at']??'')); });

    // Total: approximate by combined count (no heavy count)
    $total = count($allItems);
    if (!$all) {
        // Slice to page/limit
        $allItems = array_slice($allItems, $offset, $limit);
    }

    respond(['ok'=>true, 'total'=>$total, 'items'=>$allItems]);
} catch (Throwable $e) {
    respond(['ok'=>false, 'error'=>'Failed to load notifications'], 500);
}
