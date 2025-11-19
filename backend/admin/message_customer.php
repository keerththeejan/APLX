<?php
require_once __DIR__ . '/../init.php';
require_admin();
function __pick_table(mysqli $conn, array $candidates){ foreach ($candidates as $t){ try { $sql = "SELECT 1 FROM `{$t}` LIMIT 1"; if ($stmt = $conn->prepare($sql)) { $stmt->execute(); $stmt->close(); return $t; } } catch (Throwable $e) {} } return $candidates[0] ?? 'customer'; }
function __column_exists(mysqli $conn, string $table, string $col){ try { $sql = "SHOW COLUMNS FROM `{$table}` LIKE ?"; if (!$stmt = $conn->prepare($sql)) return false; $stmt->bind_param('s', $col); $stmt->execute(); $stmt->store_result(); $ok = $stmt->num_rows > 0; $stmt->close(); return $ok; } catch (Throwable $e) { return false; } }
function __pick_col(mysqli $conn, string $table, array $cands, string $fallback){ foreach ($cands as $c) { if (__column_exists($conn, $table, $c)) return $c; } return $fallback; }
$TABLE = __pick_table($conn, ['customer','customers']);
$COL_ID   = __pick_col($conn, $TABLE, ['id','customer_id'], 'id');
$COL_NAME = __pick_col($conn, $TABLE, ['name','customer_name','full_name'], 'name');
$COL_EMAIL= __pick_col($conn, $TABLE, ['email','customer_email'], 'email');

$msg = '';
$error = '';

// Load customers for dropdown using mapped table/columns
$customers = [];
try {
  $sql = "SELECT {$COL_ID} id, {$COL_NAME} name, {$COL_EMAIL} email FROM `{$TABLE}` ORDER BY {$COL_NAME} ASC";
  if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $customers[] = $row; }
  }
} catch (Throwable $e) { $customers = []; }

// Prefill values from GET for convenience when navigating from frontend HTML
$prefill_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$prefill_subject = trim($_GET['subject'] ?? '');
$prefill_message = trim($_GET['message'] ?? '');
$prefill_email = trim($_GET['customer_email'] ?? '');

// Accept either POST (preferred with CSRF) or GET (from frontend 'Continue to Send')
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$isGetSubmit = ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['subject']) && isset($_GET['message']));
if ($isPost || $isGetSubmit) {
    if ($isPost) { csrf_check(); }
    $src = $isPost ? $_POST : $_GET;
    $customer_id = intval($src['customer_id'] ?? 0);
    $customer_email = trim($src['customer_email'] ?? '');
    $subject = trim($src['subject'] ?? '');
    $message = trim($src['message'] ?? '');

    if ($subject === '' || $message === '') {
        $error = 'Subject and message are required.';
        if ($isPost) {
            $em = urlencode($error);
            header('Location: /APLX/frontend/admin/message_customer.php?err=' . $em);
            exit;
        }
    } else {
        $toName = '';
        $toEmail = '';

        if ($customer_id > 0) {
            // Resolve by ID
            $sql = "SELECT {$COL_NAME} name, {$COL_EMAIL} email FROM `{$TABLE}` WHERE {$COL_ID} = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $customer_id);
            $stmt->execute();
            $cust = $stmt->get_result()->fetch_assoc();
            if ($cust) { $toName = $cust['name'] ?? ''; $toEmail = $cust['email'] ?? ''; }
        }

        // Fallback to provided email if id not set or no record
        if (!$toEmail && $customer_email) { $toEmail = $customer_email; }

        if (!$toEmail || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Valid customer email is required.';
            if ($isPost) {
                $em = urlencode($error);
                header('Location: /APLX/frontend/admin/message_customer.php?err=' . $em);
                exit;
            }
        } else {
            $company = $COMPANY_NAME ?? 'Parcel Transport';
            $greeting = $toName ? ('<p>Dear ' . h($toName) . ',</p>') : '';
            $htmlBody = '<div style="font-family:Segoe UI,Arial,sans-serif;font-size:14px;color:#111">'
                . $greeting
                . '<div>' . nl2br(h($message)) . '</div>'
                . '<p style="margin-top:18px">Regards,<br>' . h($company) . '</p>'
                . '</div>';

            if (send_mail($toEmail, $subject, $htmlBody)) {
                $msg = 'Message sent to ' . h($toEmail) . ($toName ? (' (' . h($toName) . ')') : '') . '.';
                // Also record in admin_mailbox as an outgoing message for future threads
                try {
                    $conn->query("CREATE TABLE IF NOT EXISTS admin_mailbox (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        direction ENUM('in','out') NOT NULL,
                        from_email VARCHAR(255) NOT NULL,
                        to_email VARCHAR(255) NOT NULL,
                        subject VARCHAR(255) NOT NULL,
                        body TEXT NOT NULL,
                        reply_to_id INT NULL,
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_dir_created (direction, created_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                    $from = $SUPPORT_EMAIL ?? 'admin@localhost';
                    $plain = strip_tags($message);
                    $stmtMb = $conn->prepare('INSERT INTO admin_mailbox(direction, from_email, to_email, subject, body, reply_to_id) VALUES (\'out\', ?, ?, ?, ?, NULL)');
                    $stmtMb->bind_param('ssss', $from, $toEmail, $subject, $plain);
                    $stmtMb->execute();
                } catch (Throwable $e) { /* ignore mailbox log errors */ }

                // If coming from frontend POST, redirect back with success flag for clean UX
                if ($isPost) {
                    $toParam = urlencode($toEmail);
                    header('Location: /APLX/frontend/admin/message_customer.php?sent=1&to=' . $toParam);
                    exit;
                }
            } else {
                $error = 'Failed to send email. Please verify mail configuration.';
                if ($isPost) {
                    $em = urlencode($error);
                    header('Location: /APLX/frontend/admin/message_customer.php?err=' . $em);
                    exit;
                }
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


