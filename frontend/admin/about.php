<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();

// Ensure schema
$conn->query("CREATE TABLE IF NOT EXISTS about_section (
  id TINYINT PRIMARY KEY,
  eyebrow VARCHAR(150) NOT NULL DEFAULT 'Safe Transportation & Logistics',
  title VARCHAR(250) NOT NULL DEFAULT '',
  subtext VARCHAR(400) NOT NULL DEFAULT '',
  image_url VARCHAR(600) NOT NULL DEFAULT '',
  feature1_icon_text VARCHAR(16) NULL,
  feature1_icon_url VARCHAR(512) NULL,
  feature1_title VARCHAR(140) NOT NULL DEFAULT '',
  feature1_desc VARCHAR(240) NOT NULL DEFAULT '',
  feature2_icon_text VARCHAR(16) NULL,
  feature2_icon_url VARCHAR(512) NULL,
  feature2_title VARCHAR(140) NOT NULL DEFAULT '',
  feature2_desc VARCHAR(240) NOT NULL DEFAULT '',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$conn->query("INSERT IGNORE INTO about_section (id) VALUES (1)");

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
$row = [];
$res = $conn->query('SELECT * FROM about_section WHERE id=1');
$row = $res->fetch_assoc() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  csrf_check();
  $eyebrow = trim($_POST['eyebrow'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $subtext = trim($_POST['subtext'] ?? '');
  $imgUp = save_upload_img('image_file','about');
  $image_url = $imgUp ?: trim($_POST['image_url'] ?? '');

  $f1_icon_text = trim($_POST['f1_icon_text'] ?? '');
  $f1_icon_up = save_upload_img('f1_icon_file','about/icons');
  $f1_title = trim($_POST['f1_title'] ?? '');
  $f1_desc = trim($_POST['f1_desc'] ?? '');

  $f2_icon_text = trim($_POST['f2_icon_text'] ?? '');
  $f2_icon_up = save_upload_img('f2_icon_file','about/icons');
  $f2_title = trim($_POST['f2_title'] ?? '');
  $f2_desc = trim($_POST['f2_desc'] ?? '');

  // keep old icon urls if no new upload
  $old = $conn->query('SELECT image_url, feature1_icon_url, feature2_icon_url FROM about_section WHERE id=1')->fetch_assoc() ?: [];
  if (!$image_url) $image_url = $old['image_url'] ?? '';
  $f1_icon_url = $f1_icon_up ?: ($old['feature1_icon_url'] ?? null);
  $f2_icon_url = $f2_icon_up ?: ($old['feature2_icon_url'] ?? null);

  $stmt = $conn->prepare('UPDATE about_section SET eyebrow=?, title=?, subtext=?, image_url=?, feature1_icon_text=?, feature1_icon_url=?, feature1_title=?, feature1_desc=?, feature2_icon_text=?, feature2_icon_url=?, feature2_title=?, feature2_desc=? WHERE id=1');
  $stmt->bind_param('ssssssssssss', $eyebrow, $title, $subtext, $image_url, $f1_icon_text, $f1_icon_url, $f1_title, $f1_desc, $f2_icon_text, $f2_icon_url, $f2_title, $f2_desc);
  $stmt->execute();
  $msg = 'Saved';
  header('Location: /APLX/frontend/admin/about.php?msg='.urlencode($msg));
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | About Section</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    @media (max-width: 900px){.grid2{grid-template-columns:1fr;}}
    .card input, .card textarea, .card select{background:#0b1220;border:1px solid var(--border);color:var(--text);border-radius:8px;padding:10px;width:100%;color-scheme:dark}
    .card input::placeholder, .card textarea::placeholder{color:var(--muted)}
    .image-thumb{width:64px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--border)}
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
      <h2>About Section</h2>
      <?php if (isset($_GET['msg'])): ?><div class="notice"><?php echo h($_GET['msg']); ?></div><?php endif; ?>
      <form class="stack" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
        <div class="grid2">
          <div>
            <label>Eyebrow<input name="eyebrow" value="<?php echo h($row['eyebrow'] ?? ''); ?>" placeholder="Safe Transportation & Logistics"></label>
            <label>Title<input name="title" value="<?php echo h($row['title'] ?? ''); ?>" placeholder="Modern transport system & secure packaging"></label>
            <label>Subtext<textarea name="subtext" rows="3" placeholder="We combine real‑time visibility ..."><?php echo h($row['subtext'] ?? ''); ?></textarea></label>
            <label>Upload Main Image<input type="file" name="image_file" accept="image/*"></label>
            <label>Or Image URL<input name="image_url" placeholder="https://..." value="<?php echo h($row['image_url'] ?? ''); ?>"></label>
          </div>
          <div>
            <fieldset class="stack">
              <legend>Feature #1</legend>
              <label>Icon Emoji<input name="f1_icon_text" placeholder="🏬" value="<?php echo h($row['feature1_icon_text'] ?? ''); ?>"></label>
              <label>Icon Image<input type="file" name="f1_icon_file" accept="image/*"></label>
              <?php if (!empty($row['feature1_icon_url'])): ?><div class="muted"><img class="image-thumb" src="<?php echo h($row['feature1_icon_url']); ?>" alt=""></div><?php endif; ?>
              <label>Title<input name="f1_title" value="<?php echo h($row['feature1_title'] ?? ''); ?>" placeholder="Air Freight Transportation"></label>
              <label>Description<input name="f1_desc" value="<?php echo h($row['feature1_desc'] ?? ''); ?>" placeholder="Fast air cargo across regions."></label>
            </fieldset>
            <fieldset class="stack">
              <legend>Feature #2</legend>
              <label>Icon Emoji<input name="f2_icon_text" placeholder="🚢" value="<?php echo h($row['feature2_icon_text'] ?? ''); ?>"></label>
              <label>Icon Image<input type="file" name="f2_icon_file" accept="image/*"></label>
              <?php if (!empty($row['feature2_icon_url'])): ?><div class="muted"><img class="image-thumb" src="<?php echo h($row['feature2_icon_url']); ?>" alt=""></div><?php endif; ?>
              <label>Title<input name="f2_title" value="<?php echo h($row['feature2_title'] ?? ''); ?>" placeholder="Ocean Freight Transportation"></label>
              <label>Description<input name="f2_desc" value="<?php echo h($row['feature2_desc'] ?? ''); ?>" placeholder="Cost‑effective global lanes."></label>
            </fieldset>
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
