<?php
// backend/migrations.php

function run_migrations(mysqli $conn): void {
    try {
        $res = $conn->query("SHOW TABLES LIKE 'bookings'");
        if ($res && $res->num_rows > 0) {
            $col = $conn->query("SHOW COLUMNS FROM bookings LIKE 'sender_email'");
            if ($col && $col->num_rows === 0) {
                $conn->query("ALTER TABLE bookings ADD COLUMN sender_email VARCHAR(150) NOT NULL AFTER sender_name");
            }
        }
    } catch (Throwable $e) {
        // ignore migration errors at runtime
    }
}
