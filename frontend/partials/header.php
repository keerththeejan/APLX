<?php
require_once __DIR__ . '/../../backend/init.php';
$u = $_SESSION['user'] ?? null;
$isAdmin = $u && (($u['role'] ?? '') === 'admin');
?>
<header class="navbar">
  <div class="container">
    <div class="brand"><span class="brand-icon" aria-hidden="true">🚚</span> Parcel Transport</div>
    <button id="themeToggle" class="theme-toggle centered" title="Toggle theme" aria-pressed="false">☀️/🌙</button>
    <nav>
      <a href="/APLX/" class="<?php echo ($_SERVER['REQUEST_URI'] ?? '') === '/APLX/' ? 'active' : ''; ?>">Home</a>
      <a href="/APLX/frontend/track.php">Track</a>
      <a id="navBook" href="/APLX/frontend/login.php?next=%2FAPLX%2Ffrontend%2Fcustomer%2Fbook.php">Book</a>
      <a href="/APLX/frontend/login.php" title="Login" aria-label="Login"><span aria-hidden="true">👤</span> <span class="hide-sm">Login</span></a>
      <?php if ($isAdmin) { ?>
        <a href="/APLX/frontend/admin/dashboard.php" title="Admin Dashboard" aria-label="Admin Dashboard"><span aria-hidden="true">🛡️</span> <span class="hide-sm">Admin</span></a>
      <?php } ?>
    </nav>
  </div>
</header>
