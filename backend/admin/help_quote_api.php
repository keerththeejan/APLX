<?php
// backend/admin/help_quote_api.php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

// Ensure table
$conn->query("CREATE TABLE IF NOT EXISTS help_quote (
  id TINYINT PRIMARY KEY,
  eyebrow VARCHAR(120) DEFAULT NULL,
  title VARCHAR(200) DEFAULT NULL,
  subtext VARCHAR(600) DEFAULT NULL,
  bullets_text TEXT NULL,
  mini_image_url VARCHAR(600) DEFAULT NULL,
  mini_title VARCHAR(200) DEFAULT NULL,
  mini_sub VARCHAR(200) DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

function respond($data,$code=200){ http_response_code($code); echo json_encode($data); exit; }

if ($action === 'csrf') { echo json_encode(['csrf' => csrf_token()]); exit; }

if ($method === 'GET') {
  $q = $conn->query('SELECT * FROM help_quote WHERE id=1');
  $row = $q ? ($q->fetch_assoc() ?: []) : [];
  respond(['item' => $row]);
}

csrf_check();

if ($method === 'POST') {
  $eyebrow = trim($_POST['eyebrow'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $subtext = trim($_POST['subtext'] ?? '');
  $bullets_text = trim($_POST['bullets_text'] ?? '');
  $mini_title = trim($_POST['mini_title'] ?? '');
  $mini_sub = trim($_POST['mini_sub'] ?? '');

  // Optional upload for mini image
  $mini_image_url = null;
  if (isset($_FILES['mini_image']) && is_uploaded_file($_FILES['mini_image']['tmp_name'])){
    $file = $_FILES['mini_image'];
    if ($file['error'] === UPLOAD_ERR_OK){
      $finfo = @finfo_open(FILEINFO_MIME_TYPE);
      $mime = $finfo ? finfo_buffer($finfo, file_get_contents($file['tmp_name'])) : ($file['type'] ?? '');
      $allowed = ['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp','image/gif'=>'.gif'];
      if (isset($allowed[$mime])){
        $root = realpath(__DIR__ . '/../../');
        $dir = $root . '/uploads/help_quote';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        if (is_dir($dir)){
          $name = bin2hex(random_bytes(8)) . $allowed[$mime];
          $target = $dir . '/' . $name;
          if (move_uploaded_file($file['tmp_name'], $target)){
            $mini_image_url = '/APLX/uploads/help_quote/' . $name;
          }
        }
      }
    }
  }
  // If no upload, allow URL field
  if (!$mini_image_url){
    $tmp = trim($_POST['mini_image_url'] ?? '');
    if ($tmp !== '') $mini_image_url = $tmp;
  }

  $stmt = $conn->prepare('INSERT INTO help_quote (id, eyebrow, title, subtext, bullets_text, mini_image_url, mini_title, mini_sub) VALUES (1,?,?,?,?,?,?,?)
                           ON DUPLICATE KEY UPDATE eyebrow=VALUES(eyebrow), title=VALUES(title), subtext=VALUES(subtext), bullets_text=VALUES(bullets_text), mini_image_url=VALUES(mini_image_url), mini_title=VALUES(mini_title), mini_sub=VALUES(mini_sub)');
  $stmt->bind_param('sssssss', $eyebrow,$title,$subtext,$bullets_text,$mini_image_url,$mini_title,$mini_sub);
  $stmt->execute();
  respond(['ok'=>true]);
}

respond(['error'=>'Unsupported method'],405);
