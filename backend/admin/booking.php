<?php
require_once __DIR__ . '/../init.php';
require_admin();
$msg = '';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $sender = trim($_POST['sender_name'] ?? '');
    $receiver = trim($_POST['receiver_name'] ?? '');
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;
    $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : null;
    if ($sender && $receiver && $origin && $destination && $weight > 0) {
        $tracking = strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
        $status = 'Booked';
        $stmt = $conn->prepare('INSERT INTO shipments (tracking_number, sender_name, receiver_name, origin, destination, weight, price, status) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->bind_param('sssssdss', $tracking, $sender, $receiver, $origin, $destination, $weight, $price, $status);
        $stmt->execute();
        $msg = 'Shipment created. Tracking: ' . h($tracking);
    } else {
        $err = 'Please fill all required fields.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Booking</title>
  <link rel="stylesheet" href="/Parcel/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand">Admin</div>
    <nav>
      <a href="/Parcel/backend/admin/dashboard.php">Dashboard</a>
      <a href="/Parcel/backend/admin/profile.php">Profile</a>
      <a href="/Parcel/backend/admin/booking.php" class="active">Booking</a>
      <a href="/Parcel/backend/admin/shipments.php">Shipments</a>
      <a href="/Parcel/backend/admin/analytics.php">Analytics</a>
      <a href="/Parcel/backend/admin/settings.php">Settings</a>
      <a href="/Parcel/backend/admin/contact.php">Contact</a>
      <a href="/Parcel/backend/auth_logout.php">Logout</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <h2>Create Booking</h2>
    <?php if ($msg): ?><p class="notice"><?php echo $msg; ?></p><?php endif; ?>
    <?php if ($err): ?><p class="error"><?php echo h($err); ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
      <div class="grid">
        <input type="text" name="sender_name" placeholder="Sender Name" required>
        <input type="text" name="receiver_name" placeholder="Receiver Name" required>
        <input type="text" name="origin" placeholder="Origin City" required>
        <input type="text" name="destination" placeholder="Destination City" required>
        <input type="number" step="0.01" name="weight" placeholder="Weight (kg)" required>
        <input type="number" step="0.01" name="price" placeholder="Price (optional)">
      </div>
      <button class="btn" type="submit">Create Booking</button>
      <a class="btn btn-outline" href="/Parcel/frontend/customer/book.html" style="margin-left:8px">Use Customer Form</a>
    </form>
  </section>
</main>
</body>
</html>
