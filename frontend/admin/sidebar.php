<?php /* Admin Sidebar partial */ 
require_once __DIR__ . '/../../backend/init.php';
$u = $_SESSION['user'] ?? null;
$name = trim((string)($u['name'] ?? 'Admin'));
$email = trim((string)($u['email'] ?? 'admin@parcel.local'));
$initials = function($n){ $p = preg_split('/\s+/', trim((string)$n)); $a = strtoupper($p[0][0] ?? 'A'); $b = strtoupper(($p[1][0] ?? '')); return $a . $b; };
?>
<aside class="sidebar">
  <div>
    <div class="side-header">
      <div class="logo">📦</div>
      <div class="app">Admin Panel</div>
    </div>
    <nav>
      <a href="/APLX/frontend/admin/dashboard.php"><span class="icon">🏠</span><span>Dashboard</span></a>
      <a href="/APLX/frontend/admin/users.php"><span class="icon">👤</span><span>Users</span></a>
      <a href="/APLX/frontend/admin/customers.php"><span class="icon">👥</span><span>Customers</span></a>
      <a href="/APLX/frontend/admin/mail.php"><span class="icon">✉️</span><span>Mail</span></a>
      <a href="/APLX/frontend/admin/shipments.php"><span class="icon">📦</span><span>Shipments</span></a>
      <a href="/APLX/frontend/admin/booking.php"><span class="icon">📝</span><span>Bookings</span></a>
      <a href="/APLX/frontend/admin/analytics.php"><span class="icon">📊</span><span>Analytics</span></a>
      <a href="/APLX/frontend/admin/settings.php"><span class="icon">⚙️</span><span>Settings</span></a>
      <a href="/APLX/frontend/admin/hero_banners.php"><span class="icon">🖼️</span><span>Hero Banners</span></a>
    </nav>
  </div>
  <div class="side-user">
    <div class="avatar"><?php echo htmlspecialchars($initials($name)); ?></div>
    <div class="meta">
      <div class="name"><?php echo htmlspecialchars($name ?: 'Admin'); ?></div>
      <div class="muted"><?php echo htmlspecialchars($email ?: 'admin@parcel.local'); ?></div>
    </div>
  </div>
</aside>
