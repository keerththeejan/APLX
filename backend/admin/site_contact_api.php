<?php
// backend/admin/site_contact_api.php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
if ($action === 'csrf') { echo json_encode(['csrf' => csrf_token()]); exit; }

$method = $_SERVER['REQUEST_METHOD'];

function respond($data,$code=200){ http_response_code($code); echo json_encode($data); exit; }

if ($method === 'GET') {
  $res = $conn->query('SELECT * FROM site_contact WHERE id=1');
  $row = $res->fetch_assoc() ?: [];
  respond(['item' => $row]);
}

csrf_check();

if ($method === 'POST') {
  $address = trim($_POST['address'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $hours_weekday = trim($_POST['hours_weekday'] ?? '');
  $hours_sat = trim($_POST['hours_sat'] ?? '');
  $hours_sun = trim($_POST['hours_sun'] ?? '');

  $stmt = $conn->prepare('INSERT INTO site_contact (id,address,phone,email,hours_weekday,hours_sat,hours_sun) VALUES (1,?,?,?,?,?,?)
                           ON DUPLICATE KEY UPDATE address=VALUES(address), phone=VALUES(phone), email=VALUES(email), hours_weekday=VALUES(hours_weekday), hours_sat=VALUES(hours_sat), hours_sun=VALUES(hours_sun)');
  $stmt->bind_param('ssssss', $address,$phone,$email,$hours_weekday,$hours_sat,$hours_sun);
  $stmt->execute();
  respond(['ok'=>true]);
}

respond(['error'=>'Unsupported method'],405);
