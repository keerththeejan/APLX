<?php
require_once __DIR__ . '/init.php';

// Only handle POST; otherwise bounce to frontend login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $params = ['login' => '1'];
    if (isset($_GET['next']) && $_GET['next'] !== '') { $params['next'] = $_GET['next']; }
    if (isset($_GET['stay']) && $_GET['stay'] !== '') { $params['stay'] = $_GET['stay']; }
    redirect('/APLX/?' . http_build_query($params));
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
            $p = ['login' => '1', 'status' => 'error'];
            if ($next !== '') { $p['next'] = $next; }
            if (isset($_POST['stay']) && $_POST['stay'] !== '') { $p['stay'] = $_POST['stay']; }
            redirect('/APLX/?' . http_build_query($p));
        }
    } else {
        $p = ['login' => '1', 'status' => 'error'];
        if ($next !== '') { $p['next'] = $next; }
        if (isset($_POST['stay']) && $_POST['stay'] !== '') { $p['stay'] = $_POST['stay']; }
        redirect('/APLX/?' . http_build_query($p));
    }
}

// Decide destination based on session role, with optional next override
$u = current_user();
$role = $u['role'] ?? 'customer';

// If admin (or hinted as admin), always go to admin dashboard first
if ($role === 'admin' || $roleHint === 'admin') {
    redirect('/APLX/frontend/admin/dashboard.php');
}

// Validate next: allow only in-site paths under /APLX/frontend/
if ($next) {
    $parts = @parse_url($next);
    $path = is_array($parts) ? ($parts['path'] ?? '') : '';
    // normalize legacy "/APLX/" prefix and .html extension if provided
    if (is_string($path) && strpos($path, '/APLX/') === 0) {
        $path = '/APLX/' . substr($path, strlen('/APLX/'));
    }
    if (is_string($path)) {
        $path = preg_replace('/\.html$/i', '.php', $path);
    }
    if (is_string($path) && strpos($path, '/APLX/frontend/') === 0) {
        $qs = isset($parts['query']) ? ('?' . $parts['query']) : '';
        $frag = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';
        redirect($path . $qs . $frag);
    }
}

redirect('/APLX/frontend/customer/book.php');


