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

// Inject <base href="/APLX/frontend/"> as the first element in <head>
$html = preg_replace(
    '#<head(\b[^>]*)>#i',
    '<head$1><base href="/APLX/frontend/">',
    $html,
    1
);

// Output as HTML
header('Content-Type: text/html; charset=utf-8');
echo $html;
exit;
