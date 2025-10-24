<?php
// backend/admin/customers_api.php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

function respond($data,$code=200){ http_response_code($code); echo json_encode($data); exit; }

// Helpers to adapt to schema differences without information_schema permissions
function pick_table(mysqli $conn, array $candidates): string {
  foreach ($candidates as $t) {
    try {
      $sql = "SELECT 1 FROM `{$t}` LIMIT 1";
      if ($stmt = $conn->prepare($sql)) { $stmt->execute(); $stmt->close(); return $t; }
    } catch (Throwable $e) { /* try next */ }
  }
  return $candidates[0] ?? 'customer';
}
function column_exists(mysqli $conn, string $table, string $col): bool {
  try {
    $sql = "SHOW COLUMNS FROM `{$table}` LIKE ?";
    if (!$stmt = $conn->prepare($sql)) return false;
    $stmt->bind_param('s', $col);
    $stmt->execute();
    $stmt->store_result();
    $ok = $stmt->num_rows > 0;
    $stmt->close();
    return $ok;
  } catch (Throwable $e) { return false; }
}
function pick_col(mysqli $conn, string $table, array $cands, string $fallback): string {
  foreach ($cands as $c) { if (column_exists($conn, $table, $c)) return $c; }
  return $fallback;
}
// Determine table name and available columns
$TABLE = pick_table($conn, ['customer','customers']);
// Core field mappings (alias to API keys)
$COL_ID   = pick_col($conn, $TABLE, ['id','customer_id'], 'id');
$COL_NAME = pick_col($conn, $TABLE, ['name','customer_name','full_name'], 'name');
$COL_EMAIL= pick_col($conn, $TABLE, ['email','customer_email'], 'email');
$COL_PHONE= pick_col($conn, $TABLE, ['phone','mobile','contact','phone_number'], 'phone');
$COL_ADDR = pick_col($conn, $TABLE, ['address','addr','street','address_line'], 'address');
$COL_DIST = pick_col($conn, $TABLE, ['district','city'], 'district');
$COL_PROV = pick_col($conn, $TABLE, ['province','state','region'], 'province');
$COL_STAT = pick_col($conn, $TABLE, ['status','is_active','active'], 'status');
$COL_CREATED = pick_col($conn, $TABLE, ['created_at','created','created_on','registered_at','created_date'], 'created_at');
$HAS_CREATED = column_exists($conn, $TABLE, $COL_CREATED);
$HAS_ADDRESS = column_exists($conn, $TABLE, $COL_ADDR);
$HAS_DISTRICT = column_exists($conn, $TABLE, $COL_DIST);
$HAS_PROVINCE = column_exists($conn, $TABLE, $COL_PROV);
$HAS_STATUS = column_exists($conn, $TABLE, $COL_STAT);
$HAS_PASSHASH = column_exists($conn, $TABLE, 'password_hash');

// CSRF token fetch
if ($action === 'csrf') {
  echo json_encode(['csrf' => csrf_token()]);
  exit;
}

// GET list or single
if ($method === 'GET') {
  $id = intval($_GET['id'] ?? 0);
  if ($id > 0) {
    $selects = ["`$COL_ID`   AS id","`$COL_NAME` AS name","`$COL_EMAIL` AS email","`$COL_PHONE` AS phone"];
    if ($HAS_ADDRESS)  $selects[] = "`$COL_ADDR` AS address";
    if ($HAS_DISTRICT) $selects[] = "`$COL_DIST` AS district";
    if ($HAS_PROVINCE) $selects[] = "`$COL_PROV` AS province";
    if ($HAS_STATUS)   $selects[] = "`$COL_STAT` AS status";
    if ($HAS_CREATED)  $selects[] = "`$COL_CREATED` AS created_at";
    $sql = 'SELECT '.implode(',', $selects).' FROM `'.$TABLE.'` WHERE `'.$COL_ID.'`=?';
    $stmt = $conn->prepare($sql);
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
    $selects = ["`$COL_ID`   AS id","`$COL_NAME` AS name","`$COL_EMAIL` AS email","`$COL_PHONE` AS phone"];
    if ($HAS_ADDRESS)  $selects[] = "`$COL_ADDR` AS address";
    if ($HAS_DISTRICT) $selects[] = "`$COL_DIST` AS district";
    if ($HAS_PROVINCE) $selects[] = "`$COL_PROV` AS province";
    if ($HAS_STATUS)   $selects[] = "`$COL_STAT` AS status";
    if ($HAS_CREATED)  $selects[] = "`$COL_CREATED` AS created_at";
    $sql = 'SELECT SQL_CALC_FOUND_ROWS '.implode(',', $selects).' FROM `'.$TABLE.'` WHERE `'.$COL_NAME.'` LIKE ? OR `'.$COL_EMAIL.'` LIKE ? ORDER BY `'.$COL_ID.'` DESC LIMIT ? OFFSET ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssii', $like, $like, $limit, $offset);
    $stmt->execute();
  } else {
    $selects = ["`$COL_ID`   AS id","`$COL_NAME` AS name","`$COL_EMAIL` AS email","`$COL_PHONE` AS phone"];
    if ($HAS_ADDRESS)  $selects[] = "`$COL_ADDR` AS address";
    if ($HAS_DISTRICT) $selects[] = "`$COL_DIST` AS district";
    if ($HAS_PROVINCE) $selects[] = "`$COL_PROV` AS province";
    if ($HAS_STATUS)   $selects[] = "`$COL_STAT` AS status";
    if ($HAS_CREATED)  $selects[] = "`$COL_CREATED` AS created_at";
    $sql = 'SELECT SQL_CALC_FOUND_ROWS '.implode(',', $selects).' FROM `'.$TABLE.'` ORDER BY `'.$COL_ID.'` DESC LIMIT ? OFFSET ?';
    $stmt = $conn->prepare($sql);
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
    // Common inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $password = (string)($_POST['password'] ?? '');

    if ($id <= 0) {
      // CREATE: require name and valid email as before
      if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(['error' => 'Name and valid email required'], 400);
      }
      if ($HAS_PASSHASH) {
        if ($password === '') respond(['error'=>'Password required'],400);
        $hash = password_hash($password, PASSWORD_DEFAULT);
      }
      $cols = ['name','email','phone'];
      $vals = ['sss'];
      $args = [$name,$email,$phone];
      if ($HAS_ADDRESS){ $cols[]='address'; $vals[0].='s'; $args[]=$address; }
      if ($HAS_DISTRICT){ $cols[]='district'; $vals[0].='s'; $args[]=$district; }
      if ($HAS_PROVINCE){ $cols[]='province'; $vals[0].='s'; $args[]=$province; }
      if ($HAS_STATUS){ $cols[]='status'; $vals[0].='i'; $args[]=$status; }
      if ($HAS_PASSHASH){ $cols[]='password_hash'; $vals[0].='s'; $args[]=$hash; }
      if ($HAS_CREATED){ $cols[]='created_at'; }
      $placeholders = rtrim(str_repeat('?,', count($args)), ',');
      $sql = 'INSERT INTO `'.$TABLE.'` ('.implode(',', $cols).') VALUES (' . ($HAS_CREATED? ($placeholders.($placeholders? ',':'').'NOW()') : $placeholders) . ')';
      $stmt = $conn->prepare($sql);
      $stmt->bind_param($vals[0], ...$args);
      $stmt->execute();
      respond(['ok'=>true,'id'=>$stmt->insert_id]);
    } else {
      // UPDATE: only allow status change; ignore other fields
      if (!$HAS_STATUS) { respond(['error' => 'Status column not available'], 400); }
      $sql = 'UPDATE `'.$TABLE.'` SET `'.$COL_STAT.'`=? WHERE `'.$COL_ID.'`=?';
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('ii', $status, $id);
      $stmt->execute();
      respond(['ok'=>true]);
    }
  }
  case 'DELETE': {
    $id = intval($_GET['id'] ?? ($_POST['id'] ?? 0));
    if ($id <= 0) respond(['error' => 'Invalid id'], 400);
    $stmt = $conn->prepare('DELETE FROM `'.$TABLE.'` WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['ok'=>true]);
  }
  default:
    respond(['error'=>'Unsupported method'],405);
}

