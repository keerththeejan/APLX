<?php
require_once __DIR__ . '/../init.php';
require_admin();

$msg = '';
$error = '';

// Load customers for dropdown
$stmt = $conn->prepare("SELECT u.id, u.name, u.email FROM users u WHERE u.role = 'customer' ORDER BY u.name ASC");
$stmt->execute();
$res = $stmt->get_result();

// Prefill values from GET for convenience when navigating from frontend HTML
$prefill_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$prefill_subject = trim($_GET['subject'] ?? '');
$prefill_message = trim($_GET['message'] ?? '');
$prefill_email = trim($_GET['customer_email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $customer_id = intval($_POST['customer_id'] ?? 0);
    $customer_email = trim($_POST['customer_email'] ?? '');
{{ ... }}
        if (!$cust) {
            $error = 'Customer not found.';
        } else {
            $company = $COMPANY_NAME ?? 'Parcel Transport';
            $htmlBody = '<div style="font-family:Segoe UI,Arial,sans-serif;font-size:14px;color:#111">'
                . '<p>Dear ' . h($cust['name']) . ',</p>'
                . '<div>' . nl2br(h($message)) . '</div>'
                . '<p style="margin-top:18px">Regards,<br>' . h($company) . '</p>'
                . '</div>';
            if (send_mail($cust['email'], $subject, $htmlBody)) {
                $msg = 'Message sent to ' . h($cust['name']) . ' (' . h($cust['email']) . ').';
                // Log mail in mail_logs for audit/history shown in admin Mail page
                try {
                    $conn->query("CREATE TABLE IF NOT EXISTS mail_logs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        recipient_type ENUM('admin','customer') NOT NULL,
                        recipient_email VARCHAR(255) NOT NULL,
                        subject VARCHAR(255) NOT NULL,
                        status VARCHAR(32) NOT NULL DEFAULT 'sent',
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                    $stmtLog = $conn->prepare("INSERT INTO mail_logs(recipient_type, recipient_email, subject, status) VALUES ('customer', ?, ?, 'sent')");
                    $stmtLog->bind_param('ss', $cust['email'], $subject);
                    $stmtLog->execute();
                } catch (Throwable $e) {
                    // Silent fail on logging; mail already sent
                }
            } else {
                $error = 'Failed to send email. Please verify mail configuration.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Message Customer</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand">Admin</div>
    <nav>
      <a href="/APLX/backend/admin/dashboard.php">Dashboard</a>
      <a href="/APLX/backend/admin/booking.php">Booking</a>
      <a href="/APLX/backend/admin/shipments.php">Shipments</a>
      <a href="/APLX/backend/admin/analytics.php">Analytics</a>
      <a href="/APLX/backend/admin/settings.php">Settings</a>
      <a href="/APLX/backend/admin/message_customer.php" class="active">Message Customer</a>
      <a href="/APLX/backend/auth_logout.php">Logout</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <h2>Send Message to Customer</h2>
    <?php if ($msg): ?><p class="notice"><?php echo h($msg); ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?php echo h($error); ?></p><?php endif; ?>
    <form method="post" class="form-grid">
      <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
      <div class="form-row">
        <label>Customer</label>
        <select name="customer_id">
          <option value="">-- Select customer --</option>
          <?php foreach ($customers as $c): ?>
            <option value="<?php echo (int)$c['id']; ?>" <?php echo $prefill_id === (int)$c['id'] ? 'selected' : ''; ?>><?php echo h($c['name'] . ' — ' . $c['email']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row">
        <label>Or Customer Email (optional)</label>
        <input type="email" name="customer_email" placeholder="name@example.com" value="<?php echo h($prefill_email); ?>">
      </div>
      <div class="form-row">
        <label>Subject</label>
        <input type="text" name="subject" placeholder="Subject" required value="<?php echo h($prefill_subject); ?>">
      </div>
      <div class="form-row">
        <label>Message</label>
        <textarea name="message" rows="8" placeholder="Type your message..." required><?php echo h($prefill_message); ?></textarea>
      </div>
      <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end">
        <a class="btn btn-secondary" href="/APLX/backend/admin/dashboard.php">Cancel</a>
        <button class="btn" type="submit">Send Message</button>
      </div>
    </form>
  </section>
</main>
</body>
</html>


