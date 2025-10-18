<?php
require_once __DIR__ . '/../init.php';
require_admin();
// Stats
$total = $conn->query("SELECT COUNT(*) c FROM shipments")->fetch_assoc()['c'] ?? 0;
$delivered = $conn->query("SELECT COUNT(*) c FROM shipments WHERE status='Delivered'")->fetch_assoc()['c'] ?? 0;
$inTransit = $conn->query("SELECT COUNT(*) c FROM shipments WHERE status='In Transit'")->fetch_assoc()['c'] ?? 0;
$booked = $conn->query("SELECT COUNT(*) c FROM shipments WHERE status='Booked'")->fetch_assoc()['c'] ?? 0;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand">Admin Dashboard</div>
    <nav>
      <a href="/APLX/backend/admin/dashboard.php" class="active">Dashboard</a>
      <a href="/APLX/backend/admin/shipments.php">Shipments</a>
      <a href="/APLX/backend/auth_logout.php">Logout</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="grid cards">
    <div class="stat card"><h3>Total</h3><div class="big"><?php echo htmlspecialchars($total); ?></div></div>
    <div class="stat card"><h3>Booked</h3><div class="big"><?php echo htmlspecialchars($booked); ?></div></div>
    <div class="stat card"><h3>In Transit</h3><div class="big"><?php echo htmlspecialchars($inTransit); ?></div></div>
    <div class="stat card"><h3>Delivered</h3><div class="big"><?php echo htmlspecialchars($delivered); ?></div></div>
  </section>
</main>
</body>
</html>
