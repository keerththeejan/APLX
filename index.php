<?php
// Serve the frontend home at /APLX/ without changing the URL.
// We inject a <base> tag so that all relative URLs in frontend/index.html
// continue to resolve under /APLX/frontend/.

$file = __DIR__ . '/frontend/index.html';
if (!is_file($file)) {
    http_response_code(404);
    echo 'Frontend index not found.';
    exit;
}

$html = file_get_contents($file);
if ($html === false) {
    http_response_code(500);
    echo 'Unable to read frontend index.';
    exit;
}

// Inject a dynamic <base> tag as the first element in <head>
// This ensures relative URLs within frontend/index.html resolve correctly
// whether the site is hosted at the domain root or a subdirectory (e.g., /APLX/).
// Example results:
//  - If script path is "/index.php" -> base "/frontend/"
//  - If script path is "/APLX/index.php" -> base "/APLX/frontend/"
//  - If behind deeper paths, dirname will normalize accordingly.
$scriptPath = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/';
$dir = rtrim(str_replace('\\', '/', dirname($scriptPath)), '/');
$base = ($dir === '') ? '/frontend/' : $dir . '/frontend/';

$html = preg_replace(
    '#<head(\b[^>]*)>#i',
    '<head$1><base href="' . htmlspecialchars($base, ENT_QUOTES, 'UTF-8') . '">',
    $html,
    1
);

// Output as HTML
header('Content-Type: text/html; charset=utf-8');
echo $html;
exit;
