<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();

// Ensure schema
$conn->query("CREATE TABLE IF NOT EXISTS map_section (
  id TINYINT PRIMARY KEY,
  header_title VARCHAR(150) NOT NULL DEFAULT 'Find Us Here',
  header_subtext VARCHAR(300) NOT NULL DEFAULT 'Visit our office location in Kilinochchi, Sri Lanka',
  map_embed_url VARCHAR(800) NOT NULL DEFAULT '',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$conn->query("INSERT IGNORE INTO map_section (id) VALUES (1)");

$msg = '';
$row = $conn->query('SELECT * FROM map_section WHERE id=1')->fetch_assoc() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  csrf_check();
  $header_title = trim($_POST['header_title'] ?? '');
  $header_subtext = trim($_POST['header_subtext'] ?? '');
  $map_embed_url = trim($_POST['map_embed_url'] ?? '');
  $stmt = $conn->prepare('UPDATE map_section SET header_title=?, header_subtext=?, map_embed_url=? WHERE id=1');
  $stmt->bind_param('sss', $header_title, $header_subtext, $map_embed_url);
  $stmt->execute();
  $msg = 'Saved';
  header('Location: /APLX/frontend/admin/map_section.php?msg='.urlencode($msg));
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Map/Find Us</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .card input, .card textarea{background:#0b1220;border:1px solid var(--border);color:var(--text);border-radius:8px;padding:10px;width:100%;color-scheme:dark}
    .card input::placeholder, .card textarea::placeholder{color:var(--muted)}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>
    <div class="page-actions" style="text-align:right; margin:50px 0 12px;">
      <a class="btn btn-outline" href="/APLX/frontend/admin/settings.php">← Back to Settings</a>
    </div>
    <section class="card">
      <h2>Map / Find Us</h2>
      <?php if ($msg): ?><div class="notice"><?php echo h($msg); ?></div><?php endif; ?>
      <form class="stack" method="post">
        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
        <label>Header Title<input name="header_title" value="<?php echo h($row['header_title'] ?? ''); ?>" placeholder="Find Us Here"></label>
        <label>Header Subtext<input name="header_subtext" value="<?php echo h($row['header_subtext'] ?? ''); ?>" placeholder="Visit our office location in Kilinochchi, Sri Lanka"></label>
        <label>Google Maps Embed URL<textarea name="map_embed_url" rows="3" placeholder="Paste the iframe src URL here (https://www.google.com/maps/embed?...)"><?php echo h($row['map_embed_url'] ?? ''); ?></textarea></label>
        <div class="actions" style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
          <button class="btn btn-green" type="submit">Save</button>
          <a class="btn btn-outline" href="/APLX/frontend/admin/settings.php">Cancel</a>
        </div>
      </form>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
</body>
</html>
