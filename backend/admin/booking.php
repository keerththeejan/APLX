<?php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

csrf_check();

$sender = trim($_POST['sender_name'] ?? '');
$receiver = trim($_POST['receiver_name'] ?? '');
$origin = trim($_POST['origin'] ?? '');
$destination = trim($_POST['destination'] ?? '');
$weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;
$price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : null;

if (!($sender && $receiver && $origin && $destination && $weight > 0)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please fill all required fields.']);
    exit;
}

$tracking = strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
$status = 'Booked';
$stmt = $conn->prepare('INSERT INTO shipments (tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at) VALUES (?,?,?,?,?,?,?,?, NOW())');
$stmt->bind_param('sssssdss', $tracking, $sender, $receiver, $origin, $destination, $weight, $price, $status);
$stmt->execute();

echo json_encode(['ok' => true, 'id' => $conn->insert_id, 'tracking_number' => $tracking]);
exit;
