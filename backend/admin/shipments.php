<?php
require_once __DIR__ . '/../init.php';
require_admin();

// API mode: respond with JSON for frontend usage when api=1
if (isset($_GET['api']) && $_GET['api'] == '1') {
    header('Content-Type: application/json');
    try {
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = max(1, (int)($_GET['limit'] ?? 10));
        $all    = isset($_GET['all']) && $_GET['all'] == '1';
        // Keep a sane upper bound unless 'all' is requested
        if (!$all) { $limit = min(100, $limit); }
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');

        $where = '';
        $params = [];
        $types = '';
        if ($search !== '') {
            $where = " WHERE tracking_number LIKE ? OR receiver_name LIKE ? OR sender_name LIKE ? OR origin LIKE ? OR destination LIKE ?";
            $like = "%{$search}%";
            $params = [$like, $like, $like, $like, $like];
            $types  = 'sssss';
        }

        $sqlTotal = "SELECT COUNT(*) AS c FROM shipments" . $where;
        $stmt = $conn->prepare($sqlTotal);
        if ($types) { $stmt->bind_param($types, ...$params); }
        $stmt->execute();
        $resTotal = $stmt->get_result();
        $total = (int)($resTotal->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        if ($all) {
            // Return all matching rows (no pagination)
            $sql = "SELECT id, tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at, updated_at FROM shipments" . $where . " ORDER BY updated_at DESC";
            $stmt = $conn->prepare($sql);
            if ($types) { $stmt->bind_param($types, ...$params); }
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $limit = $total > 0 ? $total : count($rows);
            $page = 1;
        } else {
            $sql = "SELECT id, tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at, updated_at FROM shipments" . $where . " ORDER BY updated_at DESC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            if ($types) {
                $types2 = $types . 'ii';
                $stmt->bind_param($types2, ...array_merge($params, [$limit, $offset]));
            } else {
                $stmt->bind_param('ii', $limit, $offset);
            }
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        echo json_encode([
            'ok' => true,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'items' => $rows,
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Failed to load shipments']);
    }
    exit;
}

// Default HTML page
$search = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM shipments';
if ($search !== '') {
    $sql .= ' WHERE tracking_number LIKE ? OR receiver_name LIKE ? OR origin LIKE ? OR destination LIKE ?';
}
$sql .= ' ORDER BY updated_at DESC LIMIT 200';
$stmt = $conn->prepare($sql);
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt->bind_param('ssss', $like, $like, $like, $like);
}
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Shipments</title>
  <link rel="stylesheet" href="/Parcel/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand">Shipments</div>
    <nav>
      <a href="/Parcel/backend/admin/dashboard.php">Dashboard</a>
      <a href="/Parcel/backend/admin/shipments.php" class="active">Shipments</a>
      <a href="/Parcel/backend/auth_logout.php">Logout</a>
    </nav>
  </div>
</header>
<main class="container">
  <section class="card">
    <form method="get" class="inline">
      <input type="text" name="q" value="<?php echo h($search); ?>" placeholder="Search by tracking, receiver, city">
      <button class="btn" type="submit">Search</button>
    </form>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Tracking</th>
            <th>Receiver</th>
            <th>From</th>
            <th>To</th>
            <th>Status</th>
            <th>Updated</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?php echo h($row['tracking_number']); ?></td>
            <td><?php echo h($row['receiver_name']); ?></td>
            <td><?php echo h($row['origin']); ?></td>
            <td><?php echo h($row['destination']); ?></td>
            <td><?php echo h($row['status']); ?></td>
            <td><?php echo h($row['updated_at']); ?></td>
            <td><a class="btn btn-sm" href="/Parcel/backend/admin/edit_shipment.php?id=<?php echo (int)$row['id']; ?>">Edit</a></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
</body>
</html>
