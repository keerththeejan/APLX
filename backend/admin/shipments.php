<?php
require_once __DIR__ . '/../init.php';
require_admin();
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
