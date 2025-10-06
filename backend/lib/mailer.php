<?php
// backend/lib/mailer.php
// Simple mail helper using PHP's mail(). For reliable delivery, configure SMTP in php.ini
// or replace this helper with PHPMailer/SMTP as needed.

function send_mail($to, $subject, $htmlBody, $textBody = '') {
    // Basic headers for HTML email
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    // Use configured SUPPORT_EMAIL if set, else default
    $fromEmail = $GLOBALS['SUPPORT_EMAIL'] ?? 'no-reply@localhost';
    $fromName  = $GLOBALS['COMPANY_NAME'] ?? 'Parcel Transport';
    $headers[] = 'From: ' . encode_addr($fromName, $fromEmail);
    $headers[] = 'Reply-To: ' . encode_addr($fromName, $fromEmail);
    $headers[] = 'X-Mailer: PHP/' . phpversion();

    // Fallback plain text body if not provided
    if (!$textBody) {
        $textBody = strip_tags($htmlBody);
    }

    // Some MTAs prefer CRLF line endings
    $headersStr = implode("\r\n", $headers);
    // Note: PHP mail() does not support alternative multipart easily without building MIME manually.
    // For simplicity, send HTML content directly. For production, switch to PHPMailer.
    return @mail($to, $subject, $htmlBody, $headersStr);
}

function encode_addr($name, $email) {
    // Encode name safely for headers
    $encoded = '=?UTF-8?B?' . base64_encode($name) . '?=';
    return sprintf('%s <%s>', $encoded, $email);
}
