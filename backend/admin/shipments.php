<?php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

function json_body(){ $raw = file_get_contents('php://input'); $d = json_decode($raw, true); return is_array($d) ? $d : []; }
function respond($d,$c=200){ http_response_code($c); echo json_encode($d); exit; }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// For write operations, require valid CSRF
if (in_array($method, ['POST','PUT','PATCH','DELETE'], true)) { csrf_check(); }

// Method override via _method for form compatibility
$_method = $_POST['_method'] ?? ($_GET['_method'] ?? null);
if ($_method) { $method = strtoupper($_method); }

try {
  switch ($method) {
    case 'GET': {
      $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
      if ($id > 0) {
        $stmt = $conn->prepare('SELECT id, tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at, updated_at FROM shipments WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        respond(['item' => $item ?: null]);
      }
      $page   = max(1, (int)($_GET['page'] ?? 1));
      $limit  = max(1, min(100, (int)($_GET['limit'] ?? 10)));
      $all    = isset($_GET['all']) && $_GET['all'] == '1';
      if ($all) { $limit = 1000; /* soft cap for safety */ }
      $offset = ($page - 1) * $limit;
      $search = trim($_GET['search'] ?? '');
      $where = '';
      $params = [];
      $types = '';
      if ($search !== '') {
        $where = " WHERE tracking_number LIKE ? OR receiver_name LIKE ? OR sender_name LIKE ? OR origin LIKE ? OR destination LIKE ?";
        $like = "%{$search}%";
        $params = [$like, $like, $like, $like, $like];
        $types  = 'sssss';
      }
      $sqlTotal = 'SELECT COUNT(*) AS c FROM shipments' . $where;
      $stmt = $conn->prepare($sqlTotal);
      if ($types) { $stmt->bind_param($types, ...$params); }
      $stmt->execute();
      $total = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
      $stmt->close();
      if ($all) {
        $sql = 'SELECT id, tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at, updated_at FROM shipments' . $where . ' ORDER BY updated_at DESC';
        $stmt = $conn->prepare($sql);
        if ($types) { $stmt->bind_param($types, ...$params); }
      } else {
        $sql = 'SELECT id, tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at, updated_at FROM shipments' . $where . ' ORDER BY updated_at DESC LIMIT ? OFFSET ?';
        $stmt = $conn->prepare($sql);
        if ($types) {
          $types2 = $types . 'ii';
          $stmt->bind_param($types2, ...array_merge($params, [$limit, $offset]));
        } else {
          $stmt->bind_param('ii', $limit, $offset);
        }
      }
      $stmt->execute();
      $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
      respond(['ok'=>true, 'page'=>$page, 'limit'=>$limit, 'total'=>$total, 'items'=>$items]);
    }
    case 'POST': {
      $sender = trim($_POST['sender_name'] ?? '');
      $receiver = trim($_POST['receiver_name'] ?? '');
      $origin = trim($_POST['origin'] ?? '');
      $destination = trim($_POST['destination'] ?? '');
      $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0.0;
      $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : null;
      $status = trim($_POST['status'] ?? 'Booked');
      if (!$sender || !$receiver || !$origin || !$destination || $weight <= 0) {
        respond(['error'=>'Missing required fields'], 400);
      }
      $tracking = strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
      $stmt = $conn->prepare('INSERT INTO shipments (tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at) VALUES (?,?,?,?,?,?,?,?, NOW())');
      $stmt->bind_param('sssssdss', $tracking, $sender, $receiver, $origin, $destination, $weight, $price, $status);
      $stmt->execute();
      respond(['ok'=>true, 'id'=>$conn->insert_id, 'tracking_number'=>$tracking], 201);
    }
    case 'PUT':
    case 'PATCH': {
      $isJson = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
      $src = $isJson ? json_body() : $_POST;
      $id = intval($_GET['id'] ?? ($src['id'] ?? 0));
      if ($id <= 0) respond(['error'=>'Invalid id'], 400);
      // Load current
      $cur = $conn->prepare('SELECT receiver_name, origin, destination, status, price, weight FROM shipments WHERE id=?');
      $cur->bind_param('i', $id);
      $cur->execute();
      $old = $cur->get_result()->fetch_assoc();
      if (!$old) respond(['error'=>'Not found'], 404);
      $receiver = trim($src['receiver_name'] ?? $old['receiver_name']);
      $origin = trim($src['origin'] ?? $old['origin']);
      $destination = trim($src['destination'] ?? $old['destination']);
      $status = trim($src['status'] ?? $old['status']);
      $price = array_key_exists('price', $src) ? ( ($src['price'] === '' || $src['price'] === null) ? null : floatval($src['price']) ) : $old['price'];
      $weight = array_key_exists('weight', $src) ? floatval($src['weight']) : $old['weight'];
      $stmt = $conn->prepare('UPDATE shipments SET receiver_name=?, origin=?, destination=?, status=?, price=?, weight=? WHERE id=?');
      $stmt->bind_param('sssssdi', $receiver, $origin, $destination, $status, $price, $weight, $id);
      $stmt->execute();
      respond(['ok'=>true]);
    }
    case 'DELETE': {
      $id = intval($_GET['id'] ?? 0);
      if ($id <= 0) respond(['error'=>'Invalid id'], 400);
      $stmt = $conn->prepare('DELETE FROM shipments WHERE id=?');
      $stmt->bind_param('i', $id);
      $stmt->execute();
      respond(['ok'=>true]);
    }
    default:
      respond(['error'=>'Method not allowed'], 405);
  }
} catch (Throwable $e) {
  respond(['error'=>'Server error'], 500);
}
