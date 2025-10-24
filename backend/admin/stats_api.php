<?php
// backend/admin/stats_api.php
header('Content-Type: application/json');
require_once __DIR__ . '/../init.php'; // provides $conn (mysqli)

try {
    // Total users (prefer customers table if present)
    $total_users = 0;
    try {
        $hasCustomers = false;
        foreach (['customer','customers'] as $t) {
            try {
                $chk = $conn->prepare("SELECT 1 FROM `{$t}` LIMIT 1");
                if ($chk) { $chk->execute(); $chk->close(); $hasCustomers = $t; break; }
            } catch (Throwable $e) { /* try next */ }
        }
        if ($hasCustomers) {
            $res = $conn->query("SELECT COUNT(*) AS c FROM `{$hasCustomers}`");
            $row = $res ? $res->fetch_assoc() : null;
            $total_users = (int)($row['c'] ?? 0);
        } else {
            $res = $conn->query("SELECT COUNT(*) AS c FROM users");
            $row = $res ? $res->fetch_assoc() : null;
            $total_users = (int)($row['c'] ?? 0);
        }
    } catch (Throwable $e) {
        // Fallback if detection fails
        $res = $conn->query("SELECT COUNT(*) AS c FROM users");
        $row = $res ? $res->fetch_assoc() : null;
        $total_users = (int)($row['c'] ?? 0);
    }

    // Total shipments
    $res = $conn->query("SELECT COUNT(*) AS c FROM shipments");
    $row = $res->fetch_assoc();
    $total_shipments = (int)($row['c'] ?? 0);

    // Active bookings = shipments with status not Delivered/Cancelled
    $res = $conn->query("SELECT COUNT(*) AS c FROM shipments WHERE status IN ('Booked','In Transit')");
    $row = $res->fetch_assoc();
    $active_bookings = (int)($row['c'] ?? 0);

    // Revenue (sum of price) from shipments
    $res = $conn->query("SELECT COALESCE(SUM(price),0) AS s FROM shipments");
    $row = $res->fetch_assoc();
    $revenue = (float)($row['s'] ?? 0);

    echo json_encode([
        'ok' => true,
        'total_users' => $total_users,
        'total_shipments' => $total_shipments,
        'active_bookings' => $active_bookings,
        'revenue' => $revenue,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to load stats']);
}
