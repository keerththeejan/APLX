<?php
require_once __DIR__ . '/../init.php';
require_admin();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    // Placeholder: in real app, send email or store message
    if ($name && $email && $subject && $message) {
        $msg = 'Message received (demo).';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Contact</title>
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
      <a href="/Parcel/backend/admin/settings.php">Settings</a>
      <a href="/Parcel/backend/admin/contact.php" class="active">Contact</a>
      <a href="/Parcel/backend/auth_logout.php">Logout</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <h2>Contact Support</h2>
    <?php if ($msg): ?><p class="notice"><?php echo h($msg); ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
      <div class="grid">
        <input type="text" name="name" placeholder="Your Name" required>
        <input type="email" name="email" placeholder="Your Email" required>
        <input type="text" name="subject" placeholder="Subject" required>
        <textarea name="message" placeholder="Message" rows="6" style="grid-column: 1/-1; width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:#0b1220; color:var(--text)"></textarea>
      </div>
      <button class="btn" type="submit">Send</button>
    </form>
  </section>
</main>
</body>
</html>
