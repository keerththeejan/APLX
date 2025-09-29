<?php
require_once __DIR__ . '/../init.php';
require_admin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Analytics</title>
  <link rel="stylesheet" href="/Parcel/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand">Admin</div>
    <nav>
      <a href="/Parcel/backend/admin/dashboard.php">Dashboard</a>
      <a href="/Parcel/backend/admin/profile.php">Profile</a>
      <a href="/Parcel/backend/admin/booking.php">Booking</a>
      <a href="/Parcel/backend/admin/shipments.php">Shipments</a>
      <a href="/Parcel/backend/admin/analytics.php" class="active">Analytics</a>
      <a href="/Parcel/backend/admin/settings.php">Settings</a>
      <a href="/Parcel/backend/admin/contact.php">Contact</a>
      <a href="/Parcel/backend/auth_logout.php">Logout</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <h2>Analytics</h2>
    <div class="grid cards">
      <div class="card"><h3>Weekly Shipments</h3><p class="muted">Charts can be added later.</p></div>
      <div class="card"><h3>Top Routes</h3><p class="muted">Data table can be added later.</p></div>
      <div class="card"><h3>Delivery SLA</h3><p class="muted">KPIs can be added later.</p></div>
    </div>
  </section>
</main>
</body>
</html>
