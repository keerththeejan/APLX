<?php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

function respond($d,$c=200){ http_response_code($c); echo json_encode($d); exit; }
function json_body(){ $raw = file_get_contents('php://input'); $d = json_decode($raw, true); return is_array($d) ? $d : []; }

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
  $id = intval($_GET['id'] ?? 0);
  if ($id <= 0) respond(['error' => 'Invalid id'], 400);
  $stmt = $conn->prepare('SELECT id, tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at, updated_at FROM shipments WHERE id=? LIMIT 1');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $item = $stmt->get_result()->fetch_assoc();
  if (!$item) respond(['error' => 'Not found'], 404);
  respond(['item' => $item]);
}

if (in_array($method, ['POST','PUT','PATCH'], true)) {
  csrf_check();
  $isJson = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
  $src = $isJson ? json_body() : $_POST;
  $id = intval($_GET['id'] ?? ($src['id'] ?? 0));
  if ($id <= 0) respond(['error' => 'Invalid id'], 400);
  $cur = $conn->prepare('SELECT receiver_name, origin, destination, status FROM shipments WHERE id=?');
  $cur->bind_param('i', $id);
  $cur->execute();
  $old = $cur->get_result()->fetch_assoc();
  if (!$old) respond(['error' => 'Not found'], 404);
  $receiver = trim($src['receiver_name'] ?? $old['receiver_name']);
  $origin = trim($src['origin'] ?? $old['origin']);
  $destination = trim($src['destination'] ?? $old['destination']);
  $status = trim($src['status'] ?? $old['status']);
  $stmt = $conn->prepare('UPDATE shipments SET receiver_name=?, origin=?, destination=?, status=? WHERE id=?');
  $stmt->bind_param('ssssi', $receiver, $origin, $destination, $status, $id);
  $stmt->execute();
  respond(['ok' => true]);
}

respond(['error' => 'Method not allowed'], 405);
