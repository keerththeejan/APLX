<?php
// backend/lib/mailer.php
// Simple mail helper using PHP's mail(). For reliable delivery, configure SMTP in php.ini
// or replace this helper with PHPMailer/SMTP as needed.

function send_mail($to, $subject, $htmlBody, $textBody = '', $replyToEmail = null, $replyToName = null) {
    // Basic headers for HTML email
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    // Use configured SUPPORT_EMAIL if set, else default
    $fromEmail = $GLOBALS['SUPPORT_EMAIL'] ?? 'no-reply@localhost';
    $fromName  = $GLOBALS['COMPANY_NAME'] ?? 'Parcel Transport';
    $headers[] = 'From: ' . encode_addr($fromName, $fromEmail);
    $rtEmail = $replyToEmail ?: $fromEmail;
    $rtName  = $replyToName  ?: $fromName;
    $headers[] = 'Reply-To: ' . encode_addr($rtName, $rtEmail);
    $headers[] = 'X-Mailer: PHP/' . phpversion();

    // Fallback plain text body if not provided
    if (!$textBody) {
        $textBody = strip_tags($htmlBody);
    }

/**
 * Best-effort classification of recipient type for logging.
 */
function infer_recipient_type($to){
    $to = strtolower(trim((string)$to));
    $support = strtolower(trim((string)($GLOBALS['SUPPORT_EMAIL'] ?? '')));
    if ($support && $to === $support) return 'admin';
    // If the local part contains 'admin' we treat as admin, else customer
    $parts = explode('@', $to, 2);
    $local = $parts[0] ?? '';
    if (strpos($local, 'admin') !== false) return 'admin';
    return 'customer';
}

    // Try PHPMailer SMTP if available and configured; else fallback to PHP mail()
    $ok = false;
    $smtpHost = $GLOBALS['SMTP_HOST']   ?? '';
    $smtpPort = (int)($GLOBALS['SMTP_PORT']   ?? 0);
    $smtpUser = $GLOBALS['SMTP_USER']   ?? '';
    $smtpPass = $GLOBALS['SMTP_PASS']   ?? '';
    $smtpSecure = strtolower((string)($GLOBALS['SMTP_SECURE'] ?? 'tls'));

    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer') && $smtpHost) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->Port = $smtpPort ?: (($smtpSecure === 'ssl') ? 465 : 587);
            $mail->SMTPAuth = (bool)($smtpUser !== '' || $smtpPass !== '');
            if ($smtpSecure === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            if ($smtpUser) { $mail->Username = $smtpUser; }
            if ($smtpPass) { $mail->Password = $smtpPass; }

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($rtEmail, $rtName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;
            $ok = $mail->send();
        } catch (\Throwable $e) {
            $ok = false; // fall through to logging as failed
        }
    } else {
        // Some MTAs prefer CRLF line endings
        $headersStr = implode("\r\n", $headers);
        // Note: PHP mail() does not support alternative multipart easily without building MIME manually.
        // For simplicity, send HTML content directly.
        $ok = @mail($to, $subject, $htmlBody, $headersStr);
    }

    // Log every outgoing mail to mail_logs for admin audit/history
    try {
        // Use global DB connection from config.php (included by init.php)
        if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            $conn = $GLOBALS['conn'];
            // Ensure table exists once
            $conn->query("CREATE TABLE IF NOT EXISTS mail_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                recipient_type ENUM('admin','customer') NOT NULL,
                recipient_email VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                status VARCHAR(32) NOT NULL DEFAULT 'sent',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $rtype = infer_recipient_type($to);
            $status = $ok ? 'sent' : 'failed';
            $stmt = $conn->prepare("INSERT INTO mail_logs(recipient_type, recipient_email, subject, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $rtype, $to, $subject, $status);
            $stmt->execute();
        }
    } catch (Throwable $e) {
        // Do not interrupt caller on logging failure
    }

    return $ok;
}

function encode_addr($name, $email) {
    // Encode name safely for headers
    $encoded = '=?UTF-8?B?' . base64_encode($name) . '?=';
    return sprintf('%s <%s>', $encoded, $email);
}
