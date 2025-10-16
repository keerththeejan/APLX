<?php
require_once __DIR__ . '/init.php';
// Public endpoint: no CSRF (HTML-only frontend)

$msg = '';
$error = '';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$district = trim($_POST['district'] ?? '');
$province = trim($_POST['province'] ?? '');
$country = trim($_POST['country'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Require all form fields present on UI
    if ($name === '' || $email === '' || $password === '' || $phone === '' || $address === '' || $district === '' || $province === '') {
        $error = 'Please fill all required fields: name, email, password, phone, address, district, and province.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // Check existing email in customer table
        $stmt = $conn->prepare('SELECT id FROM customer WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $error = 'Email already registered.';
        } else {
            // Insert into customer table
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO customer (name, email, password_hash, phone, address, district, province) VALUES (?,?,?,?,?,?,?)');
            $stmt->bind_param('sssssss', $name, $email, $hash, $phone, $address, $district, $province);
            $stmt->execute();

            // Send welcome + booking contact email
            $company   = $COMPANY_NAME ?? 'Parcel Transport';
            $support   = $SUPPORT_EMAIL ?? 'support@parcel.local';
            $booking   = $BOOKING_EMAIL ?? 'booking@parcel.local';
            $phoneNo   = $SUPPORT_PHONE ?? '';
            $addr      = $SUPPORT_ADDRESS ?? '';
            $bookUrl   = 'http://localhost/APLX/frontend/customer/book.php';

            $subject = 'Welcome to ' . $company . ' – Booking Details Inside';
            $htmlBody = '<div style="font-family:Segoe UI,Arial,sans-serif;font-size:14px;color:#111">'
                . '<h2 style="margin:0 0 12px">Welcome, ' . h($name) . '!</h2>'
                . '<p>Thank you for registering with ' . h($company) . '.</p>'
                . '<h3 style="margin:16px 0 8px">Booking Contact Details</h3>'
                . '<ul>'
                . '<li><strong>Email (Bookings):</strong> ' . h($booking) . '</li>'
                . '<li><strong>Support Email:</strong> ' . h($support) . '</li>'
                . ($phoneNo ? '<li><strong>Phone:</strong> ' . h($phoneNo) . '</li>' : '')
                . ($addr ? '<li><strong>Address:</strong> ' . h($addr) . '</li>' : '')
                . '</ul>'
                . '<p>You can start a new booking here: <a href="' . h($bookUrl) . '">' . h($bookUrl) . '</a></p>'
                . '<p style="margin-top:18px">Regards,<br>' . h($company) . '</p>'
                . '</div>';

            // Ignore failure silently in UI; optionally log in future
            @send_mail($email, $subject, $htmlBody);

            $msg = 'Registration successful. You can now log in.';
        }
    }

    // Redirect back to register form with status + message
    $status = $error ? 'error' : ($msg ? 'ok' : 'error');
    $text = $error ?: $msg ?: 'Unexpected error.';
    $qs = http_build_query(['status' => $status, 'msg' => $text]);
    header('Location: /APLX/frontend/customer/register.php?' . $qs);
    exit;
}
?>


