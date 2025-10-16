<?php
// Serve frontend/index.php internally without changing the URL.
$frontendIndex = __DIR__ . '/frontend/index.php';
$loginFile = __DIR__ . '/frontend/login.php';
if (!is_file($frontendIndex)) {
    http_response_code(404);
    echo 'Frontend index not found.';
    exit;
}

ob_start();
// Ensure relative includes inside frontend/index.php resolve correctly
$oldCwd = getcwd();
chdir(__DIR__ . '/frontend');
// If a short-lived cookie or query says to show login, render login.php instead
$hasError = (isset($_GET['status']) && strtolower($_GET['status']) === 'error');
$requestedLogin = (
    (isset($_COOKIE['show_login']) && $_COOKIE['show_login'] === '1') ||
    (isset($_GET['login']) && $_GET['login'] === '1') ||
    $hasError
);
if ($requestedLogin && is_file($loginFile)) {
    if (isset($_COOKIE['show_login'])) {
        // Clear the one-shot cookie
        setcookie('show_login', '', time() - 3600, '/APLX');
        unset($_COOKIE['show_login']);
    }
    // Prevent caching of the error/login view
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
    }
    include $loginFile;
} else {
    include $frontendIndex;
}
$cwdRestored = chdir($oldCwd);
$output = ob_get_clean();

// Inject a dynamic <base> tag to ensure relative assets resolve under /APLX/frontend/
$scriptPath = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/';
$dir = rtrim(str_replace('\\', '/', dirname($scriptPath)), '/');
$base = ($dir === '') ? '/frontend/' : $dir . '/frontend/';

if (stripos($output, '<base ') === false) {
    $output = preg_replace(
        '#<head(\b[^>]*)>#i',
        '<head$1><base href="' . htmlspecialchars($base, ENT_QUOTES, 'UTF-8') . '">',
        $output,
        1
    );
}

header('Content-Type: text/html; charset=utf-8');
echo $output;
exit;
