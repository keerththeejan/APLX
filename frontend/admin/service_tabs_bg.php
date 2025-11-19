<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();

// Ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS services_tabs_settings (
  id INT PRIMARY KEY,
  bg_image_url VARCHAR(600) NOT NULL DEFAULT '',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Ensure row id=1 exists
try {
  $conn->query("INSERT INTO services_tabs_settings (id, bg_image_url) VALUES (1, '') ON DUPLICATE KEY UPDATE bg_image_url = bg_image_url");
} catch (Throwable $e) { /* ignore */ }

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

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  csrf_check();
  $uploaded = save_upload_img('bg_file', 'services_tabs');
  $bg = $uploaded ?: trim((string)($_POST['bg_image_url'] ?? ''));
  if (!$bg){ $err = 'Image required'; }
  if (!$err){
    $stmt = $conn->prepare('UPDATE services_tabs_settings SET bg_image_url=? WHERE id=1');
    $stmt->bind_param('s', $bg);
    $stmt->execute();
    $msg = 'Saved';
    header('Location: /APLX/frontend/admin/service_tabs_bg.php?msg='.urlencode($msg));
    exit;
  }
}

// Load current
$cur = ['bg_image_url' => ''];
try {
  if ($r = $conn->query('SELECT * FROM services_tabs_settings WHERE id=1')){
    $row = $r->fetch_assoc(); if ($row) $cur = $row;
  }
} catch (Throwable $e) { /* ignore */ }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Services Tabs Background</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .image-preview{max-width:100%;max-height:260px;border:1px solid var(--border);border-radius:12px;display:block}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>
    <div class="page-actions" style="text-align:right; margin:50px 0 12px;">
      <a class="btn btn-outline" href="/APLX/frontend/admin/settings.php">← Back to Settings</a>
      <a class="btn btn-outline" href="/APLX/frontend/admin/service_tabs.php">Open Service Tabs</a>
    </div>
    <section class="card">
      <h2>Services Tabs Background</h2>
      <?php if (isset($_GET['msg'])): ?><div class="notice"><?php echo h($_GET['msg']); ?></div><?php endif; ?>
      <?php if ($err): ?><div class="error"><?php echo h($err); ?></div><?php endif; ?>
      <form class="stack" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
        <label>Background Image URL<input type="text" name="bg_image_url" placeholder="https://..." value="<?php echo h($cur['bg_image_url'] ?? ''); ?>"></label>
        <label>Or Upload Background Image<input type="file" name="bg_file" accept="image/*"></label>
        <?php if (!empty($cur['bg_image_url'])): ?>
          <img class="image-preview" src="<?php echo h($cur['bg_image_url']); ?>" alt="Preview">
        <?php endif; ?>
        <div style="display:flex;gap:8px;justify-content:flex-end;">
          <button class="btn" type="submit">Save</button>
          <a class="btn btn-outline" href="/APLX/frontend/index.php" target="_blank">View Site</a>
        </div>
      </form>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
</body>
</html>
