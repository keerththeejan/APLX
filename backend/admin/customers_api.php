<?php
// backend/admin/customers_api.php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

function respond($data,$code=200){ http_response_code($code); echo json_encode($data); exit; }

// CSRF token fetch
if ($action === 'csrf') {
  echo json_encode(['csrf' => csrf_token()]);
  exit;
}

// GET list or single
if ($method === 'GET') {
  $id = intval($_GET['id'] ?? 0);
  if ($id > 0) {
    $stmt = $conn->prepare('SELECT id,name,email,phone,address,district,province,status,created_at FROM customer WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    respond(['item' => $item]);
  }
  $page = max(1, intval($_GET['page'] ?? 1));
  $limit = min(100, max(1, intval($_GET['limit'] ?? 10)));
  $offset = ($page - 1) * $limit;
  $search = trim((string)($_GET['search'] ?? ''));
  if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare('SELECT SQL_CALC_FOUND_ROWS id,name,email,phone,address,district,province,status,created_at FROM customer WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->bind_param('ssii', $like, $like, $limit, $offset);
    $stmt->execute();
  } else {
    $stmt = $conn->prepare('SELECT SQL_CALC_FOUND_ROWS id,name,email,phone,address,district,province,status,created_at FROM customer ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
  }
  $res = $stmt->get_result();
  $items = [];
  while ($row = $res->fetch_assoc()) { $items[] = $row; }
  $total = 0;
  if ($r2 = $conn->query('SELECT FOUND_ROWS() AS t')) { $total = (int)($r2->fetch_assoc()['t'] ?? 0); }
  respond(['items' => $items, 'total' => $total, 'page' => $page, 'limit' => $limit]);
}

// Write operations require CSRF
csrf_check();

// Method override via _method param/form
$_method = $_POST['_method'] ?? ($_GET['_method'] ?? null);
if ($_method) { $method = strtoupper($_method); }

switch ($method) {
  case 'POST': { // create or update if id provided
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $password = (string)($_POST['password'] ?? '');
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      respond(['error' => 'Name and valid email required'], 400);
    }
    if ($id <= 0) {
      if ($password === '') respond(['error'=>'Password required'],400);
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare('INSERT INTO customer (name,email,password_hash,phone,address,district,province,status,created_at) VALUES (?,?,?,?,?,?,?,?, NOW())');
      $stmt->bind_param('sssssssi', $name,$email,$hash,$phone,$address,$district,$province,$status);
      $stmt->execute();
      respond(['ok'=>true,'id'=>$stmt->insert_id]);
    } else {
      if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE customer SET name=?, email=?, password_hash=?, phone=?, address=?, district=?, province=?, status=? WHERE id=?');
        $stmt->bind_param('sssssssii', $name,$email,$hash,$phone,$address,$district,$province,$status,$id);
      } else {
        $stmt = $conn->prepare('UPDATE customer SET name=?, email=?, phone=?, address=?, district=?, province=?, status=? WHERE id=?');
        $stmt->bind_param('ssssssii', $name,$email,$phone,$address,$district,$province,$status,$id);
      }
      $stmt->execute();
      respond(['ok'=>true]);
    }
  }
  case 'DELETE': {
    $id = intval($_GET['id'] ?? ($_POST['id'] ?? 0));
    if ($id <= 0) respond(['error' => 'Invalid id'], 400);
    $stmt = $conn->prepare('DELETE FROM customer WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['ok'=>true]);
  }
  default:
    respond(['error'=>'Unsupported method'],405);
}
