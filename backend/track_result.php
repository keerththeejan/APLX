<?php
require_once __DIR__ . '/init.php';
$tracking = trim($_GET['tn'] ?? '');
$shipment = null;
if ($tracking !== '') {
    $stmt = $conn->prepare('SELECT * FROM shipments WHERE tracking_number = ? LIMIT 1');
    $stmt->bind_param('s', $tracking);
    $stmt->execute();
    $shipment = $stmt->get_result()->fetch_assoc();
}

// If client expects JSON, return structured result (used by booking popup)
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
if (strpos($accept, 'application/json') !== false) {
    header('Content-Type: application/json');
    if ($tracking === '') {
        echo json_encode(['ok' => false, 'message' => 'No tracking number provided.']);
    } elseif ($shipment) {
        echo json_encode([
            'ok' => true,
            'tracking' => $shipment['tracking_number'],
            'status' => $shipment['status'],
            'origin' => $shipment['origin'],
            'destination' => $shipment['destination'],
            'receiver_name' => $shipment['receiver_name'],
            'updated_at' => $shipment['updated_at'],
        ]);
    } else {
        echo json_encode(['ok' => false, 'message' => 'No shipment found for tracking number: ' . $tracking]);
    }
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Track Result</title>
  <link rel="stylesheet" href="/Parcel/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand">Parcel Transport</div>
    <nav>
      <a href="/Parcel/frontend/index.html">Home</a>
      <a href="/Parcel/frontend/track.html" class="active">Track</a>
      <a href="/Parcel/frontend/customer/book.html">Book</a>
      <a href="/Parcel/frontend/auth/login.html">Admin</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <h2>Track Result</h2>
    <?php if ($tracking === ''): ?>
      <p class="muted">No tracking number provided.</p>
    <?php elseif ($shipment): ?>
      <div class="result">
        <p><strong>Tracking:</strong> <?php echo h($shipment['tracking_number']); ?></p>
        <p><strong>Status:</strong> <?php echo h($shipment['status']); ?></p>
        <p><strong>From:</strong> <?php echo h($shipment['origin']); ?> → <strong>To:</strong> <?php echo h($shipment['destination']); ?></p>
        <p><strong>Receiver:</strong> <?php echo h($shipment['receiver_name']); ?></p>
        <p><strong>Updated:</strong> <?php echo h($shipment['updated_at']); ?></p>
      </div>
    <?php else: ?>
      <p class="muted">No shipment found for tracking number: <strong><?php echo h($tracking); ?></strong></p>
    <?php endif; ?>
    <p style="margin-top:12px"><a class="btn btn-outline" href="/Parcel/frontend/track.html">Back to Track</a></p>
  </section>
</main>
</body>
</html>
