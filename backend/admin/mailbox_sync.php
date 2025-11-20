<?php
// backend/admin/mailbox_sync.php
// Syncs recent emails from the support inbox into admin_mailbox as 'in' messages
// Requirements: PHP IMAP extension enabled; Gmail IMAP enabled; using App Password
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

function respond($data, int $code = 200){ http_response_code($code); echo json_encode($data); exit; }

try {
    if (!function_exists('imap_open')) {
        respond(['ok'=>false, 'error'=>'IMAP extension not enabled'], 500);
    }

    $host = 'imap.gmail.com';
    $port = 993;
    $flags = '/imap/ssl/novalidate-cert';
    $user = (string)($GLOBALS['SMTP_USER'] ?? '');
    $pass = (string)($GLOBALS['SMTP_PASS'] ?? '');
    if ($user === '' || $pass === '') {
        respond(['ok'=>false, 'error'=>'Missing IMAP credentials'], 400);
    }

    // Ensure mailbox table exists
    $GLOBALS['conn']->query("CREATE TABLE IF NOT EXISTS admin_mailbox (
        id INT AUTO_INCREMENT PRIMARY KEY,
        direction ENUM('in','out') NOT NULL,
        from_email VARCHAR(255) NOT NULL,
        to_email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body TEXT NOT NULL,
        reply_to_id INT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_dir_created (direction, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $mailbox = sprintf('{%s:%d%s}INBOX', $host, $port, $flags);
    $inbox = @imap_open($mailbox, $user, $pass, OP_READONLY, 1);
    if (!$inbox) {
        respond(['ok'=>false, 'error'=>'IMAP connection failed'], 500);
    }

    // Fetch unseen messages only (last 100 for safety)
    $emails = @imap_search($inbox, 'UNSEEN', SE_UID);
    $imported = 0;
    if (is_array($emails) && count($emails) > 0) {
        // Limit to recent batch
        rsort($emails); // newest first
        $emails = array_slice($emails, 0, 100);
        foreach ($emails as $uid) {
            $header = @imap_headerinfo($inbox, imap_msgno($inbox, $uid));
            if (!$header) { continue; }
            $fromEmail = '';
            if (!empty($header->from) && is_array($header->from)) {
                $p = $header->from[0];
                $fromEmail = strtolower(trim(($p->mailbox ?? '') . '@' . ($p->host ?? '')));
            }
            $toEmail = strtolower(trim($GLOBALS['SUPPORT_EMAIL'] ?? $user));
            $subject = isset($header->subject) ? imap_utf8($header->subject) : '';
            $dateStr = isset($header->date) ? date('Y-m-d H:i:s', strtotime($header->date)) : date('Y-m-d H:i:s');

            // Get plain text body if possible
            $body = '';
            $structure = @imap_fetchstructure($inbox, $uid, FT_UID);
            if ($structure && isset($structure->parts) && count($structure->parts)) {
                // multipart
                for ($i=0; $i<count($structure->parts); $i++) {
                    $part = $structure->parts[$i];
                    $isText = ($part->type === 0);
                    $subtype = strtoupper($part->subtype ?? '');
                    if ($isText && $subtype === 'PLAIN') {
                        $section = ($i+1);
                        $body = @imap_fetchbody($inbox, $uid, (string)$section, FT_UID);
                        if ($part->encoding === 3) { $body = base64_decode($body); }
                        elseif ($part->encoding === 4) { $body = quoted_printable_decode($body); }
                        break;
                    }
                }
                if ($body === '') {
                    // fallback to first part
                    $body = @imap_body($inbox, $uid, FT_UID);
                }
            } else {
                // single part
                $body = @imap_body($inbox, $uid, FT_UID);
            }
            $body = is_string($body) ? trim($body) : '';

            // Deduplicate crude: avoid inserting identical recent items
            $stmt = $GLOBALS['conn']->prepare('SELECT id FROM admin_mailbox WHERE direction="in" AND from_email=? AND to_email=? AND subject=? AND created_at >= (NOW() - INTERVAL 7 DAY) ORDER BY id DESC LIMIT 1');
            $stmt->bind_param('sss', $fromEmail, $toEmail, $subject);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            if ($exists) { continue; }

            $ins = $GLOBALS['conn']->prepare('INSERT INTO admin_mailbox(direction, from_email, to_email, subject, body, reply_to_id) VALUES ("in", ?, ?, ?, ?, NULL)');
            $ins->bind_param('ssss', $fromEmail, $toEmail, $subject, $body);
            if ($ins->execute()) { $imported++; }
        }
    }
    @imap_close($inbox);
    respond(['ok'=>true, 'imported'=>$imported]);
} catch (Throwable $e) {
    respond(['ok'=>false, 'error'=>'Server error'], 500);
}
