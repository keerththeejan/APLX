<?php
// backend/lib/utils.php

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function random_token($length = 16) {
    return bin2hex(random_bytes($length));
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = random_token(16);
    }
    return $_SESSION['csrf'];
}

function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = $_POST['csrf'] ?? '';
        if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            exit;
        }
    }
}
?>
