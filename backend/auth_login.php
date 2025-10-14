<?php
require_once __DIR__ . '/init.php';

// Only handle POST; otherwise bounce to frontend login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/APLX/Parcel/frontend/login.html');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$roleHint = trim($_POST['role'] ?? ''); // 'admin' or 'customer' (optional hint)
$next = $_POST['next'] ?? '';

// Authenticate via backend/lib/auth.php (admin_profile, users, customer)
// If the role hint was 'customer' and login failed, retry without hint to allow admin creds.
if (!login($conn, $email, $password, $roleHint)) {
    if (strtolower($roleHint) === 'customer') {
        if (!login($conn, $email, $password, '')) {
            $qs = http_build_query(['status' => 'error']);
            redirect('/APLX/Parcel/frontend/login.html?' . $qs);
        }
    } else {
        $qs = http_build_query(['status' => 'error']);
        redirect('/APLX/Parcel/frontend/login.html?' . $qs);
    }
}

// Decide destination based on session role, with optional next override
$u = current_user();
$role = $u['role'] ?? 'customer';

// If admin (or hinted as admin), always go to admin dashboard first
if ($role === 'admin' || $roleHint === 'admin') {
    redirect('/APLX/Parcel/frontend/admin/dashboard.html');
}

// Validate next: allow only in-site paths under /APLX/Parcel/
if ($next) {
    $parts = @parse_url($next);
    $path = is_array($parts) ? ($parts['path'] ?? '') : '';
    if (is_string($path) && strpos($path, '/APLX/Parcel/') === 0) {
        $qs = isset($parts['query']) ? ('?' . $parts['query']) : '';
        $frag = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';
        redirect($path . $qs . $frag);
    }
}

redirect('/APLX/Parcel/frontend/customer/book.html');
