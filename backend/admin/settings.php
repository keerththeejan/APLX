<?php
require_once __DIR__ . '/../init.php';
require_admin();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    // Placeholder: normally you'd persist settings in a table.
    $msg = 'Settings saved (demo).';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Settings</title>
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
      <a href="/Parcel/backend/admin/analytics.php">Analytics</a>
      <a href="/Parcel/backend/admin/settings.php" class="active">Settings</a>
      <a href="/Parcel/backend/admin/contact.php">Contact</a>
      <a href="/Parcel/backend/auth_logout.php">Logout</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <h2>Settings</h2>
    <?php if ($msg): ?><p class="notice"><?php echo h($msg); ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
      <div class="grid">
        <input type="text" name="company" placeholder="Company Name" value="Parcel Transport">
        <input type="email" name="support_email" placeholder="Support Email" value="support@parcel.local">
        <input type="text" name="phone" placeholder="Phone" value="+91-00000-00000">
        <input type="text" name="address" placeholder="Address" value="Chennai, TN">
      </div>
      <button class="btn" type="submit">Save</button>
    </form>
  </section>
</main>
</body>
</html>
