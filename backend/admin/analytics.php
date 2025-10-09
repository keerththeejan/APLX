<?php
require_once __DIR__ . '/../init.php';
require_admin();

// API mode to serve JSON analytics when api=1
if (isset($_GET['api']) && $_GET['api'] == '1') {
  header('Content-Type: application/json');
  try {
    // Shipments per day (last 7 days)
    $q1 = $conn->query("SELECT DATE(updated_at) AS d, COUNT(*) AS c FROM shipments WHERE updated_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(updated_at) ORDER BY d ASC");
    $by_day = [];
    while ($r = $q1->fetch_assoc()) { $by_day[] = $r; }

    // Weekly status counts (last 7 days)
    $q2 = $conn->query("SELECT status, COUNT(*) AS c FROM shipments WHERE updated_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY status");
    $by_status = [];
    while ($r = $q2->fetch_assoc()) { $by_status[$r['status']] = (int)$r['c']; }

    // Yearly by month (last 12 months inclusive by created_at)
    $q4 = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS c FROM shipments WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH) GROUP BY ym ORDER BY ym ASC");
    $year_by_month = [];
    while ($r = $q4->fetch_assoc()) { $year_by_month[] = ['ym' => $r['ym'], 'c' => (int)$r['c']]; }

    // Top 5 routes
    $stmt = $conn->prepare("SELECT origin, destination, COUNT(*) AS c FROM shipments GROUP BY origin, destination ORDER BY c DESC LIMIT 5");
    $stmt->execute();
    $res = $stmt->get_result();
    $top_routes = [];
    while ($r = $res->fetch_assoc()) {
      $top_routes[] = [
        'route' => $r['origin'] . ' → ' . $r['destination'],
        'count' => (int)$r['c'],
      ];
    }

    // Delivery SLA metrics (Delivered only)
    $q3 = $conn->query("SELECT TIMESTAMPDIFF(HOUR, created_at, updated_at) AS h FROM shipments WHERE status='Delivered' AND updated_at IS NOT NULL");
    $count_del = 0; $sum_hours = 0; $within_72 = 0;
    while ($r = $q3->fetch_assoc()) {
      $h = (int)$r['h'];
      $count_del++; $sum_hours += $h; if ($h <= 72) $within_72++;
    }
    $avg_hours = $count_del ? ($sum_hours / $count_del) : 0;
    $pct_72 = $count_del ? ($within_72 * 100.0 / $count_del) : 0;

    echo json_encode([
      'ok' => true,
      'by_day' => $by_day,
      'by_status' => $by_status,
      'year_by_month' => $year_by_month,
      'top_routes' => $top_routes,
      'sla' => [
        'delivered_count' => $count_del,
        'avg_hours' => $avg_hours,
        'pct_within_72h' => $pct_72,
      ],
    ]);
  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to load analytics']);
  }
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Analytics</title>
  <link rel="stylesheet" href="/Parcel/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand">Admin</div>
    <nav>
      <a href="/Parcel/backend/admin/dashboard.php">Dashboard</a>
      <a href="/Parcel/backend/admin/profile.php">Profile</a>
      <a href="/Parcel/backend/admin/booking.php">Booking</a>
      <a href="/Parcel/backend/admin/shipments.php">Shipments</a>
      <a href="/Parcel/backend/admin/analytics.php" class="active">Analytics</a>
      <a href="/Parcel/backend/admin/settings.php">Settings</a>
      <a href="/Parcel/backend/admin/contact.php">Contact</a>
      <a href="/Parcel/backend/auth_logout.php">Logout</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <h2>Analytics</h2>
    <div class="grid cards">
      <div class="card"><h3>Weekly Shipments</h3><p class="muted">Charts can be added later.</p></div>
      <div class="card"><h3>Top Routes</h3><p class="muted">Data table can be added later.</p></div>
      <div class="card"><h3>Delivery SLA</h3><p class="muted">KPIs can be added later.</p></div>
    </div>
  </section>
</main>
</body>
</html>
