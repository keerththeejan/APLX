<?php
require_once __DIR__ . '/init.php';
// CSRF check disabled for this public booking form because frontend is plain HTML without token.

$sender = trim($_POST['sender_name'] ?? '');
$senderEmail = trim($_POST['sender_email'] ?? '');
$receiver = trim($_POST['receiver_name'] ?? '');
$origin = trim($_POST['origin'] ?? '');
$destination = trim($_POST['destination'] ?? '');
$weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;
$msg = '';
$tracking = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic email validation
    $emailOk = filter_var($senderEmail, FILTER_VALIDATE_EMAIL) !== false;
    if ($sender && $emailOk && $receiver && $origin && $destination && $weight > 0) {
        // Ensure bookings.sender_email column exists (one-time migration)
        try {
            $colCheck = $conn->query("SHOW COLUMNS FROM bookings LIKE 'sender_email'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $conn->query("ALTER TABLE bookings ADD COLUMN sender_email VARCHAR(150) NOT NULL AFTER sender_name");
            }
        } catch (Throwable $e) { /* ignore migration errors */ }
        $tracking = strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
        $status = 'Booked';
        // Always insert without price so it stores as NULL
        $stmt = $conn->prepare('INSERT INTO shipments (tracking_number, sender_name, receiver_name, origin, destination, weight, status) VALUES (?,?,?,?,?,?,?)');
        $stmt->bind_param('sssssds', $tracking, $sender, $receiver, $origin, $destination, $weight, $status);
        $stmt->execute();
        // Log booking without price + store sender_email
        $stmt2 = $conn->prepare('INSERT INTO bookings (tracking_number, sender_name, sender_email, receiver_name, origin, destination, weight) VALUES (?,?,?,?,?,?,?)');
        $stmt2->bind_param('ssssssd', $tracking, $sender, $senderEmail, $receiver, $origin, $destination, $weight);
        $stmt2->execute();
        $company = $COMPANY_NAME ?? 'Parcel Transport';
        $subject = 'Booking Confirmation - Tracking ' . $tracking;
        $htmlBody = '<div style="font-family:Segoe UI,Arial,sans-serif;font-size:14px;color:#111">'
            . '<h2 style="margin:0 0 12px">Thank you for your booking</h2>'
            . '<p>Your shipment has been booked successfully.</p>'
            . '<ul style="line-height:1.6">'
            . '<li><strong>Tracking:</strong> ' . h($tracking) . '</li>'
            . '<li><strong>Sender:</strong> ' . h($sender) . '</li>'
            . '<li><strong>Receiver:</strong> ' . h($receiver) . '</li>'
            . '<li><strong>From:</strong> ' . h($origin) . '</li>'
            . '<li><strong>To:</strong> ' . h($destination) . '</li>'
            . '<li><strong>Weight:</strong> ' . h(number_format($weight,2)) . ' kg</li>'
            . '</ul>'
            . '<p>You can track your shipment anytime.</p>'
            . '<p style="margin-top:18px">Regards,<br>' . h($company) . '</p>'
            . '</div>';
        @send_mail($senderEmail, $subject, $htmlBody);
        $msg = 'Shipment booked! Your tracking number is ' . htmlspecialchars($tracking, ENT_QUOTES, 'UTF-8') . '. A confirmation email has been sent to ' . htmlspecialchars($senderEmail, ENT_QUOTES, 'UTF-8') . '.';
    } else {
        $msg = 'Please fill all required fields with a valid email.';
    }
}

// If client expects JSON, return API-style response for AJAX
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
if (strpos($accept, 'application/json') !== false) {
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => (bool)$tracking,
        'message' => $msg,
        'tracking' => $tracking,
    ]);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Booking Result</title>
  <link rel="stylesheet" href="/Parcel/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand">Parcel Transport</div>
    <nav>
      <a href="/Parcel/frontend/index.html">Home</a>
      <a href="/Parcel/frontend/track.html">Track</a>
      <a href="/Parcel/frontend/customer/book.html">Book</a>
      <a href="/Parcel/frontend/auth/login.html">Admin</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <h2>Booking Result</h2>
    <p class="<?php echo $tracking ? 'notice' : 'error'; ?>"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php if ($tracking): ?>
      <p>Track here: <a class="btn btn-outline" href="/Parcel/backend/track_result.php?tn=<?php echo urlencode($tracking); ?>">View Status</a></p>
    <?php endif; ?>
    <p style="margin-top:12px"><a class="btn" href="/Parcel/frontend/customer/book.html">Back to Booking</a></p>
  </section>
</main>
</body>
</html>
