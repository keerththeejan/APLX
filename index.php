<?php
// Serve frontend/index.php internally without changing the URL.
$frontendIndex = __DIR__ . '/frontend/index.php';
if (!is_file($frontendIndex)) {
    http_response_code(404);
    echo 'Frontend index not found.';
    exit;
}

ob_start();
// Ensure relative includes inside frontend/index.php resolve correctly
$oldCwd = getcwd();
chdir(__DIR__ . '/frontend');
// Always load the main frontend; dedicated login pages are under /frontend/
include $frontendIndex;
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
