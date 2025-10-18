<?php
require_once __DIR__ . '/init.php';

// Only handle POST; otherwise bounce to frontend login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $next = isset($_GET['next']) ? (string)$_GET['next'] : '';
    $role = isset($_GET['role']) ? strtolower((string)$_GET['role']) : '';
    $isAdminNext = false;
    if ($next) {
        $parts = @parse_url($next);
        $path = is_array($parts) ? ($parts['path'] ?? '') : '';
        if (is_string($path) && strpos($path, '/APLX/frontend/admin/') === 0) {
            $isAdminNext = true;
        }
    }
    if ($role === 'admin' || $isAdminNext) {
        $qs = $next ? ('?next=' . rawurlencode($next)) : '';
        redirect('/APLX/frontend/admin/login.php' . $qs);
    }
    $qs = $next ? ('?next=' . rawurlencode($next)) : '';
    redirect('/APLX/frontend/login.php' . $qs);
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$roleHint = trim($_POST['role'] ?? ''); // 'admin' or 'customer' (optional hint)
$next = $_POST['next'] ?? '';

// Authenticate via backend/lib/auth.php (admin_profile, users, customer)
// If the role hint was 'customer' and login failed, retry without hint to allow admin creds.
if (!login($conn, $email, $password, $roleHint)) {
    $hint = strtolower($roleHint);
    // determine if next points to admin area
    $isAdminNext = false;
    if ($next) {
        $parts = @parse_url($next);
        $path = is_array($parts) ? ($parts['path'] ?? '') : '';
        if (is_string($path) && strpos($path, '/APLX/frontend/admin/') === 0) { $isAdminNext = true; }
    }
    if ($hint === 'customer') {
        if (!login($conn, $email, $password, '')) {
            $qs = http_build_query(array_filter([
                'status' => 'error',
                'next' => $next ?: null,
                'stay' => isset($_POST['stay']) && $_POST['stay'] !== '' ? $_POST['stay'] : null,
            ]));
            redirect('/APLX/frontend/login.php' . ($qs ? ('?' . $qs) : ''));
        }
    } else if ($hint === 'admin' || $isAdminNext) {
        $qs = http_build_query(array_filter([
            'status' => 'error',
            'next' => $next ?: null,
            'stay' => isset($_POST['stay']) && $_POST['stay'] !== '' ? $_POST['stay'] : null,
        ]));
        redirect('/APLX/frontend/admin/login.php' . ($qs ? ('?' . $qs) : ''));
    } else {
        $qs = http_build_query(array_filter([
            'status' => 'error',
            'next' => $next ?: null,
            'stay' => isset($_POST['stay']) && $_POST['stay'] !== '' ? $_POST['stay'] : null,
        ]));
        redirect('/APLX/frontend/login.php' . ($qs ? ('?' . $qs) : ''));
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


