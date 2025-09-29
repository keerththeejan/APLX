<?php
require_once __DIR__ . '/init.php';
// Public endpoint: no CSRF (HTML-only frontend)

$msg = '';
$error = '';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$country = trim($_POST['country'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check existing email
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $error = 'Email already registered.';
        } else {
            // Create user with role=customer
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer';
            $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)');
            $stmt->bind_param('ssss', $name, $email, $hash, $role);
            $stmt->execute();
            $user_id = $conn->insert_id;

            // Insert into customers table
            $stmt = $conn->prepare('INSERT INTO customers (user_id) VALUES (?)');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();

            // Create/update profile if optional fields provided
            if ($phone || $address || $city || $state || $country || $pincode) {
                $stmt = $conn->prepare('INSERT INTO user_profiles (user_id, phone, address, city, state, country, pincode) VALUES (?,?,?,?,?,?,?)');
                $stmt->bind_param('issssss', $user_id, $phone, $address, $city, $state, $country, $pincode);
                $stmt->execute();
            }

            $msg = 'Registration successful. You can now log in.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Customer Registration</title>
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
      <a href="/Parcel/frontend/customer/register.html" class="active">Register</a>
      <a href="/Parcel/frontend/auth/login.html">Admin</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <h2>Customer Registration</h2>
    <?php if ($msg): ?><p class="notice"><?php echo h($msg); ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?php echo h($error); ?></p><?php endif; ?>
    <p><a class="btn btn-outline" href="/Parcel/frontend/auth/login.html">Go to Login</a></p>
  </section>
</main>
</body>
</html>
