<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();

// Ensure schema (matches migration 0008)
$conn->query("CREATE TABLE IF NOT EXISTS home_stats (
  id TINYINT UNSIGNED NOT NULL DEFAULT 1 PRIMARY KEY,
  hero_title VARCHAR(200) NOT NULL DEFAULT 'We Provide Full Assistance in Freight & Warehousing',
  hero_subtext VARCHAR(500) NOT NULL DEFAULT 'Comprehensive ocean, air, and land freight backed by modern warehousing. Track, optimize, and scale with confidence.',
  image_url VARCHAR(600) NOT NULL DEFAULT '',
  stat1_number VARCHAR(40) NOT NULL DEFAULT '35+',
  stat1_label VARCHAR(120) NOT NULL DEFAULT 'Countries Represented',
  stat2_number VARCHAR(40) NOT NULL DEFAULT '853+',
  stat2_label VARCHAR(120) NOT NULL DEFAULT 'Projects completed',
  stat3_number VARCHAR(40) NOT NULL DEFAULT '35+',
  stat3_label VARCHAR(120) NOT NULL DEFAULT 'Total Revenue',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$conn->query("INSERT IGNORE INTO home_stats (id) VALUES (1)");

function ensure_dir($p){ if (!is_dir($p)) { @mkdir($p, 0775, true); } return is_dir($p); }
function save_upload_img($field, $subdir){
  if (!isset($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) return null;
  $file = $_FILES[$field];
  if ($file['error'] !== UPLOAD_ERR_OK) return null;
  $finfo = @finfo_open(FILEINFO_MIME_TYPE);
  $mime = $finfo ? finfo_buffer($finfo, file_get_contents($file['tmp_name'])) : ($file['type'] ?? '');
  $allowed = ['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp','image/gif'=>'.gif'];
  if (!isset($allowed[$mime])) return null;
  $root = realpath(__DIR__ . '/../../');
  $dir = $root . '/uploads/' . trim($subdir,'/');
  if (!ensure_dir($dir)) return null;
  $name = bin2hex(random_bytes(8)) . $allowed[$mime];
  $target = $dir . '/' . $name;
  if (!move_uploaded_file($file['tmp_name'], $target)) return null;
  return '/APLX/uploads/' . trim($subdir,'/') . '/' . $name;
}

$msg = '';
$err = '';
$row = $conn->query('SELECT * FROM home_stats WHERE id=1')->fetch_assoc() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  csrf_check();
  $hero_title = trim($_POST['hero_title'] ?? '');
  $hero_subtext = trim($_POST['hero_subtext'] ?? '');
  $imgUp = save_upload_img('image_file', 'home_stats');
  $image_url = $imgUp ?: trim($_POST['image_url'] ?? '');
  // keep old if empty
  if (!$image_url) { $image_url = $row['image_url'] ?? ''; }

  $s1n = trim($_POST['stat1_number'] ?? '');
  $s1l = trim($_POST['stat1_label'] ?? '');
  $s2n = trim($_POST['stat2_number'] ?? '');
  $s2l = trim($_POST['stat2_label'] ?? '');
  $s3n = trim($_POST['stat3_number'] ?? '');
  $s3l = trim($_POST['stat3_label'] ?? '');

  $stmt = $conn->prepare('UPDATE home_stats SET hero_title=?, hero_subtext=?, image_url=?, stat1_number=?, stat1_label=?, stat2_number=?, stat2_label=?, stat3_number=?, stat3_label=? WHERE id=1');
  $stmt->bind_param('sssssssss', $hero_title, $hero_subtext, $image_url, $s1n, $s1l, $s2n, $s2l, $s3n, $s3l);
  $stmt->execute();
  $msg = 'Saved';
  header('Location: /APLX/frontend/admin/home_stats.php?msg='.urlencode($msg));
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Home Stats</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    @media (max-width: 900px){.grid{grid-template-columns:1fr;}}
    .card input, .card textarea, .card select{background:#0b1220;border:1px solid var(--border);color:var(--text);border-radius:8px;padding:10px;width:100%;color-scheme:dark}
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
      <h2>Home Stats</h2>
      <?php if (isset($_GET['msg'])): ?><div class="notice"><?php echo h($_GET['msg']); ?></div><?php endif; ?>
      <form class="stack" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
        <div class="grid">
          <div>
            <label>Title<input name="hero_title" value="<?php echo h($row['hero_title'] ?? ''); ?>" placeholder="We Provide Full Assistance in Freight & Warehousing" required></label>
            <label>Subtext<textarea name="hero_subtext" rows="3" placeholder="Comprehensive ocean, air, and land freight ..." required><?php echo h($row['hero_subtext'] ?? ''); ?></textarea></label>
            <label>Upload Image<input type="file" name="image_file" accept="image/*"></label>
            <label>Or Image URL<input name="image_url" placeholder="https://..." value="<?php echo h($row['image_url'] ?? ''); ?>"></label>
          </div>
          <div>
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:12px">
              <label>Stat 1 Number<input name="stat1_number" value="<?php echo h($row['stat1_number'] ?? ''); ?>" placeholder="35+" required></label>
              <label>Stat 1 Label<input name="stat1_label" value="<?php echo h($row['stat1_label'] ?? ''); ?>" placeholder="Countries Represented" required></label>
              <label>Stat 2 Number<input name="stat2_number" value="<?php echo h($row['stat2_number'] ?? ''); ?>" placeholder="853+" required></label>
              <label>Stat 2 Label<input name="stat2_label" value="<?php echo h($row['stat2_label'] ?? ''); ?>" placeholder="Projects completed" required></label>
              <label>Stat 3 Number<input name="stat3_number" value="<?php echo h($row['stat3_number'] ?? ''); ?>" placeholder="35+" required></label>
              <label>Stat 3 Label<input name="stat3_label" value="<?php echo h($row['stat3_label'] ?? ''); ?>" placeholder="Total Revenue" required></label>
            </div>
          </div>
        </div>
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
