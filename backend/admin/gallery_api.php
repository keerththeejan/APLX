<?php
// backend/admin/gallery_api.php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($action === 'csrf') { echo json_encode(['csrf' => csrf_token()]); exit; }

function json_body(){ $raw = file_get_contents('php://input'); $d = json_decode($raw, true); return is_array($d) ? $d : []; }
function respond($d,$c=200){ http_response_code($c); echo json_encode($d); exit; }

if ($method === 'GET') {
  $q = $conn->query('SELECT id, image_url, tag, day, month, sort_order FROM gallery ORDER BY sort_order, id');
  $rows = [];
  while ($row = $q->fetch_assoc()) { $rows[] = $row; }
  respond(['items' => $rows]);
}

csrf_check();
$_method = $_POST['_method'] ?? ($_GET['_method'] ?? null);
if ($_method) $method = strtoupper($_method);

// Upload helpers
function ensure_dir($p){ if (!is_dir($p)) { @mkdir($p, 0775, true); } return is_dir($p); }
function save_upload($field, $subdir){
  if (!isset($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) return null;
  $file = $_FILES[$field];
  if ($file['error'] !== UPLOAD_ERR_OK) return null;
  $finfo = @finfo_open(FILEINFO_MIME_TYPE);
  $mime = $finfo ? finfo_buffer($finfo, file_get_contents($file['tmp_name'])) : ($file['type'] ?? '');
  $allowed = ['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp','image/gif'=>'.gif'];
  if (!isset($allowed[$mime])) return null;
  $root = realpath(__DIR__ . '/../../');
  $dir = $root . '/uploads/' . trim($subdir,'/');
  if (!ensure_dir($dir)) return null;
  $name = bin2hex(random_bytes(8)) . $allowed[$mime];
  $target = $dir . '/' . $name;
  if (!move_uploaded_file($file['tmp_name'], $target)) return null;
  return '/APLX/uploads/' . trim($subdir,'/') . '/' . $name;
}

switch ($method) {
  case 'POST':
    // Prefer uploaded file over URL
    $image_url = save_upload('image_file', 'gallery');
    if (!$image_url) { $image_url = trim($_POST['image_url'] ?? ''); }
    $tag = trim($_POST['tag'] ?? '');
    $day = $_POST['day'] !== '' ? intval($_POST['day']) : null;
    $month = trim($_POST['month'] ?? '');
    $sort_order = intval($_POST['sort_order'] ?? 0);
    if (!$image_url) respond(['error' => 'Image URL required'], 400);
    $stmt = $conn->prepare('INSERT INTO gallery(image_url, tag, day, month, sort_order) VALUES (?,?,?,?,?)');
    $stmt->bind_param('ssisi', $image_url, $tag, $day, $month, $sort_order);
    $stmt->execute();
    respond(['ok'=>true,'id'=>$stmt->insert_id]);
  case 'PUT':
  case 'PATCH':
    $isJson = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
    $src = $isJson ? json_body() : $_POST;
    $id = intval($_GET['id'] ?? ($src['id'] ?? 0));
    if ($id<=0) respond(['error'=>'Invalid id'],400);
    // Current image
    $cur = $conn->prepare('SELECT image_url FROM gallery WHERE id=?');
    $cur->bind_param('i', $id);
    $cur->execute();
    $old = ($cur->get_result()->fetch_assoc()['image_url'] ?? '');
    $tag = trim($src['tag'] ?? '');
    $day = isset($src['day']) && $src['day'] !== '' ? intval($src['day']) : null;
    $month = trim($src['month'] ?? '');
    $sort_order = intval($src['sort_order'] ?? 0);
    $newUpload = save_upload('image_file', 'gallery');
    $image_url = $newUpload ?: trim(($src['image_url'] ?? '')) ?: $old;
    if (!$image_url) respond(['error' => 'Image URL required'], 400);
    $stmt = $conn->prepare('UPDATE gallery SET image_url=?, tag=?, day=?, month=?, sort_order=? WHERE id=?');
    $stmt->bind_param('ssissi', $image_url, $tag, $day, $month, $sort_order, $id);
    $stmt->execute();
    respond(['ok'=>true]);
  case 'DELETE':
    $id = intval($_GET['id'] ?? 0);
    if ($id<=0) respond(['error'=>'Invalid id'],400);
    $stmt = $conn->prepare('DELETE FROM gallery WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['ok'=>true]);
  default:
    respond(['error'=>'Unsupported method'],405);
}
