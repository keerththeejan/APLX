<?php
require_once __DIR__ . '/../init.php';
require_admin();
$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT * FROM shipments WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$shipment = $stmt->get_result()->fetch_assoc();
if (!$shipment) { echo 'Shipment not found'; exit; }
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $receiver = trim($_POST['receiver_name'] ?? '');
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $status = trim($_POST['status'] ?? 'Booked');
    $stmt = $conn->prepare('UPDATE shipments SET receiver_name=?, origin=?, destination=?, status=? WHERE id=?');
    $stmt->bind_param('ssssi', $receiver, $origin, $destination, $status, $id);
    $stmt->execute();
    $msg = 'Updated successfully';
    $stmt = $conn->prepare('SELECT * FROM shipments WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $shipment = $stmt->get_result()->fetch_assoc();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Shipment</title>
  <link rel="stylesheet" href="/Parcel/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand">Edit Shipment</div>
    <nav>
      <a href="/Parcel/backend/admin/dashboard.php">Dashboard</a>
      <a href="/Parcel/backend/admin/shipments.php">Shipments</a>
      <a href="/Parcel/backend/auth_logout.php">Logout</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <?php if ($msg): ?><p class="notice"><?php echo h($msg); ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
      <div class="grid">
        <input type="text" name="receiver_name" value="<?php echo h($shipment['receiver_name']); ?>" placeholder="Receiver Name" required>
        <input type="text" name="origin" value="<?php echo h($shipment['origin']); ?>" placeholder="Origin" required>
        <input type="text" name="destination" value="<?php echo h($shipment['destination']); ?>" placeholder="Destination" required>
        <select name="status">
          <?php foreach(['Booked','In Transit','Delivered','Cancelled'] as $st): ?>
            <option value="<?php echo h($st); ?>" <?php echo $shipment['status']===$st?'selected':''; ?>><?php echo h($st); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn" type="submit">Save</button>
    </form>
  </section>
</main>
</body>
</html>
