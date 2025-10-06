<?php
require_once __DIR__ . '/../init.php';
require_admin();
$msg = '';
$err = '';
$u = current_user();

// Load current user basics
$uid = (int)$u['id'];
$stmt = $conn->prepare('SELECT name, email FROM users WHERE id = ?');
$stmt->bind_param('i', $uid);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();

// Load profile (may not exist yet)
$stmt = $conn->prepare('SELECT phone, company, address, city, state, country, pincode, updated_at FROM user_profiles WHERE user_id = ?');
$stmt->bind_param('i', $uid);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc() ?: [
    'phone' => '', 'company' => '', 'address' => '', 'city' => '', 'state' => '', 'country' => '', 'pincode' => '', 'updated_at' => null
];

// Handle profile save (name, email, profile fields)
if (($_POST['action'] ?? '') === 'save_profile') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Valid name and email are required.';
    } else {
        // Update users name + email
        $stmt = $conn->prepare('UPDATE users SET name=?, email=? WHERE id=?');
        $stmt->bind_param('ssi', $name, $email, $uid);
        $stmt->execute();

        // Upsert user_profiles
        $stmt = $conn->prepare('INSERT INTO user_profiles (user_id, phone, company, address, city, state, country, pincode) VALUES (?,?,?,?,?,?,?,?)
                                ON DUPLICATE KEY UPDATE phone=VALUES(phone), company=VALUES(company), address=VALUES(address), city=VALUES(city), state=VALUES(state), country=VALUES(country), pincode=VALUES(pincode)');
        $stmt->bind_param('isssssss', $uid, $phone, $company, $address, $city, $state, $country, $pincode);
        $stmt->execute();

        $msg = 'Profile saved.';

        // Refresh loaded data
        $stmt = $conn->prepare('SELECT name, email FROM users WHERE id = ?');
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $userRow = $stmt->get_result()->fetch_assoc();
        $stmt = $conn->prepare('SELECT phone, company, address, city, state, country, pincode, updated_at FROM user_profiles WHERE user_id = ?');
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_assoc() ?: $profile;
    }
}

// Handle password change
if (($_POST['action'] ?? '') === 'change_password') {
    csrf_check();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($new !== $confirm) {
        $err = 'New password and confirmation do not match.';
    } else {
        $isAdmin = strtolower($u['role'] ?? '') === 'admin';
        if ($isAdmin && $current === '') {
            // Allow admin to set new password without current
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET password_hash=? WHERE id=?');
            $stmt->bind_param('si', $hash, $uid);
            $stmt->execute();
            $msg = 'Password updated successfully.';
        } else {
            $stmt = $conn->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if (!$row || !password_verify($current, $row['password_hash'])) {
                $err = 'Current password is incorrect.';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE users SET password_hash=? WHERE id=?');
                $stmt->bind_param('si', $hash, $uid);
                $stmt->execute();
                $msg = 'Password updated successfully.';
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
  <title>Admin | Profile</title>
  <link rel="stylesheet" href="/Parcel/css/style.css">
  <style>
    .profile-header{display:flex;align-items:center;gap:12px;margin-bottom:12px}
    .profile-avatar{width:56px;height:56px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#16a34a;color:#04110a;font-weight:800;font-size:22px}
    .profile-meta small{color:var(--muted)}
  </style>
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand">Admin</div>
    <nav>
      <a href="/Parcel/backend/admin/dashboard.php">Dashboard</a>
      <a href="/Parcel/backend/admin/profile.php" class="active">Profile</a>
      <a href="/Parcel/backend/admin/booking.php">Booking</a>
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
    <div class="profile-header">
      <div class="profile-avatar"><?php echo strtoupper(substr($userRow['name'] ?? 'A', 0, 1)); ?></div>
      <div class="profile-meta">
        <div class="big" style="line-height:1"><?php echo h($userRow['name'] ?? ''); ?></div>
        <div><?php echo h($userRow['email'] ?? ''); ?></div>
        <small>Last updated: <?php echo h($profile['updated_at'] ?? '—'); ?></small>
      </div>
    </div>
    <?php if ($msg): ?><p class="notice"><?php echo h($msg); ?></p><?php endif; ?>
    <?php if ($err): ?><p class="error"><?php echo h($err); ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
      <input type="hidden" name="action" value="save_profile">
      <div class="grid">
        <input type="text" name="name" placeholder="Full Name" value="<?php echo h($userRow['name'] ?? ''); ?>" required>
        <input type="email" value="<?php echo h($userRow['email'] ?? ''); ?>" disabled>
        <input type="text" value="Role: <?php echo h($u['role']); ?>" disabled>
        <input type="text" name="phone" placeholder="Phone" value="<?php echo h($profile['phone']); ?>">
        <input type="text" name="company" placeholder="Company" value="<?php echo h($profile['company']); ?>">
        <input type="text" name="address" placeholder="Address" value="<?php echo h($profile['address']); ?>">
        <input type="text" name="city" placeholder="City" value="<?php echo h($profile['city']); ?>">
        <input type="text" name="state" placeholder="State" value="<?php echo h($profile['state']); ?>">
        <input type="text" name="country" placeholder="Country" value="<?php echo h($profile['country']); ?>">
        <input type="text" name="pincode" placeholder="Pincode" value="<?php echo h($profile['pincode']); ?>">
      </div>
      <button class="btn" type="submit">Save Profile</button>
    </form>
  </section>
  <section class="card">
    <h3>Change Password</h3>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
      <input type="hidden" name="action" value="change_password">
      <div class="grid">
        <input type="password" name="current_password" placeholder="Current Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
      </div>
      <button class="btn" type="submit">Update Password</button>
    </form>
  </section>
</main>
</body>
</html>
