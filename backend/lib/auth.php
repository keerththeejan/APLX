<?php
// backend/lib/auth.php

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    $u = current_user();
    return $u && ($u['role'] === 'admin');
}

function require_login() {
    if (!current_user()) {
        $next = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/APLX/';
        redirect('/APLX/frontend/login.php?next=' . rawurlencode($next));
    }
}

function require_admin() {
    if (!is_admin()) {
        $next = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/APLX/frontend/admin/dashboard.php';
        redirect('/APLX/frontend/admin/login.php?next=' . rawurlencode($next));
    }
}

function login($conn, $email, $password, $roleHint = '') {
    $hint = strtolower(trim((string)$roleHint));

    // If explicitly admin: only allow admin_profile, do not fall back
    if ($hint === 'admin') {
        // Admins authenticate exclusively via users table with role='admin' and hashed passwords
        try {
            $stmt = $conn->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $roleU = strtolower((string)($row['role'] ?? ''));
                if ($roleU === 'admin' && password_verify($password, (string)$row['password_hash'])) {
                    $_SESSION['user'] = [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'role' => 'admin',
                    ];
                    return true;
                }
            }
        } catch (Throwable $e) { /* ignore */ }
        return false; // no fallback to customer if admin was requested
    }

    // If explicitly customer: skip admin, try users/customer
    if ($hint === 'customer') {
        // Try users table first (may include roles like 'customer')
        try {
            $stmt = $conn->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                if (password_verify($password, $row['password_hash'])) {
                    $_SESSION['user'] = [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'role' => $row['role'] ?: 'customer',
                    ];
                    return true;
                }
            }
        } catch (Throwable $e) { /* ignore */ }

        // Try customer table
        try {
            $stmt = $conn->prepare('SELECT id, name, email, password_hash FROM customer WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                if (password_verify($password, $row['password_hash'])) {
                    $_SESSION['user'] = [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'role' => 'customer',
                    ];
                    return true;
                }
            }
        } catch (Throwable $e) { /* ignore */ }
        return false;
    }

    // No hint: try users first (any role), then customer; admin_profile is deprecated
    try {
        $stmt = $conn->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'role' => $row['role'] ?: 'customer',
                ];
                return true;
            }
        }
    } catch (Throwable $e) { /* ignore */ }

    try {
        $stmt = $conn->prepare('SELECT id, name, email, password_hash FROM customer WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'role' => 'customer',
                ];
                return true;
            }
        }
    } catch (Throwable $e) { /* ignore */ }
    return false;
}

function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
?>


