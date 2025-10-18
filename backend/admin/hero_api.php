<?php
// backend/admin/hero_api.php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

function respond($data,$code=200){ http_response_code($code); echo json_encode($data); exit; }

if ($action === 'csrf') { echo json_encode(['csrf' => csrf_token()]); exit; }

if ($method === 'GET') {
  $res = $conn->query('SELECT * FROM hero_settings WHERE id=1');
  $row = $res->fetch_assoc() ?: [];
  respond(['item' => $row]);
}

csrf_check();

if ($method === 'POST') {
  // Upload helpers
  $ensure_dir = function($p){ if (!is_dir($p)) { @mkdir($p, 0775, true); } return is_dir($p); };
  $save_upload = function($field, $subdir) use ($ensure_dir){
    if (!isset($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) return null;
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $finfo = @finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_buffer($finfo, file_get_contents($file['tmp_name'])) : ($file['type'] ?? '');
    $allowed = ['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp','image/gif'=>'.gif'];
    if (!isset($allowed[$mime])) { return null; }
    $root = realpath(__DIR__ . '/../../');
    $uploadDir = $root . '/uploads/' . trim($subdir, '/');
    if (!$ensure_dir($uploadDir)) return null;
    $name = bin2hex(random_bytes(8)) . $allowed[$mime];
    $target = $uploadDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $target)) return null;
    return '/APLX/uploads/' . trim($subdir,'/') . '/' . $name; // public URL
  };
  $eyebrow = trim($_POST['eyebrow'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $subtitle = trim($_POST['subtitle'] ?? '');
  $tagline = trim($_POST['tagline'] ?? '');
  $cta1_text = trim($_POST['cta1_text'] ?? '');
  $cta1_link = trim($_POST['cta1_link'] ?? '');
  $cta2_text = trim($_POST['cta2_text'] ?? '');
  $cta2_link = trim($_POST['cta2_link'] ?? '');
  // Prefer uploaded file over provided URL
  $uploaded = $save_upload('image_file', 'hero');
  $background_url = $uploaded ?: trim($_POST['background_url'] ?? '');

  $stmt = $conn->prepare('INSERT INTO hero_settings (id,eyebrow,title,subtitle,tagline,cta1_text,cta1_link,cta2_text,cta2_link,background_url) VALUES (1,?,?,?,?,?,?,?,?,?)
                          ON DUPLICATE KEY UPDATE eyebrow=VALUES(eyebrow), title=VALUES(title), subtitle=VALUES(subtitle), tagline=VALUES(tagline), cta1_text=VALUES(cta1_text), cta1_link=VALUES(cta1_link), cta2_text=VALUES(cta2_text), cta2_link=VALUES(cta2_link), background_url=VALUES(background_url)');
  $stmt->bind_param('sssssssss', $eyebrow,$title,$subtitle,$tagline,$cta1_text,$cta1_link,$cta2_text,$cta2_link,$background_url);
  $stmt->execute();
  respond(['ok' => true]);
}

respond(['error' => 'Unsupported method'], 405);
