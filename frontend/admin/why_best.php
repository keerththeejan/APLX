<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();

// Ensure schema
$conn->query("CREATE TABLE IF NOT EXISTS why_best (
  id TINYINT PRIMARY KEY,
  header_title VARCHAR(200) NOT NULL DEFAULT 'Why we are considered the best in business',
  header_subtext VARCHAR(400) NOT NULL DEFAULT 'Decentralized trade, direct transport, high flexibility and secure delivery.',
  center_image_url VARCHAR(600) NOT NULL DEFAULT '',
  bg_image_url VARCHAR(600) NOT NULL DEFAULT '',
  f1_icon_text VARCHAR(16) NULL,
  f1_icon_url VARCHAR(512) NULL,
  f1_title VARCHAR(140) NOT NULL DEFAULT '',
  f1_desc VARCHAR(240) NOT NULL DEFAULT '',
  f2_icon_text VARCHAR(16) NULL,
  f2_icon_url VARCHAR(512) NULL,
  f2_title VARCHAR(140) NOT NULL DEFAULT '',
  f2_desc VARCHAR(240) NOT NULL DEFAULT '',
  f3_icon_text VARCHAR(16) NULL,
  f3_icon_url VARCHAR(512) NULL,
  f3_title VARCHAR(140) NOT NULL DEFAULT '',
  f3_desc VARCHAR(240) NOT NULL DEFAULT '',
  f4_icon_text VARCHAR(16) NULL,
  f4_icon_url VARCHAR(512) NULL,
  f4_title VARCHAR(140) NOT NULL DEFAULT '',
  f4_desc VARCHAR(240) NOT NULL DEFAULT '',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$conn->query("INSERT IGNORE INTO why_best (id) VALUES (1)");

// Lightweight migration: add bg_image_url if the table existed before
try {
  $colCheck = $conn->query("SHOW COLUMNS FROM why_best LIKE 'bg_image_url'");
  if ($colCheck && $colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE why_best ADD COLUMN bg_image_url VARCHAR(600) NOT NULL DEFAULT ''");
  }
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

$row = $conn->query('SELECT * FROM why_best WHERE id=1')->fetch_assoc() ?: [];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  csrf_check();
  $header_title = trim($_POST['header_title'] ?? '');
  $header_subtext = trim($_POST['header_subtext'] ?? '');
  $imgUp = save_upload_img('center_image_file','why_best');
  $center_image_url = $imgUp ?: trim($_POST['center_image_url'] ?? '');
  if (!$center_image_url && !empty($row['center_image_url'])) $center_image_url = $row['center_image_url'];

  // Background image (for section backdrop)
  $bgUp = save_upload_img('bg_image_file','why_best/bg');
  $bg_image_url = $bgUp ?: trim($_POST['bg_image_url'] ?? '');
  if (!$bg_image_url && !empty($row['bg_image_url'])) $bg_image_url = $row['bg_image_url'];

  $f = [];
  for ($i=1;$i<=4;$i++){
    $icon_text = trim($_POST["f{$i}_icon_text"] ?? '');
    $icon_up = save_upload_img("f{$i}_icon_file", 'why_best/icons');
    $title = trim($_POST["f{$i}_title"] ?? '');
    $desc = trim($_POST["f{$i}_desc"] ?? '');
    $oldUrl = $row["f{$i}_icon_url"] ?? null;
    $icon_url = $icon_up ?: $oldUrl;
    $f[$i] = compact('icon_text','icon_url','title','desc');
  }

  $stmt = $conn->prepare('UPDATE why_best SET header_title=?, header_subtext=?, center_image_url=?, bg_image_url=?, f1_icon_text=?, f1_icon_url=?, f1_title=?, f1_desc=?, f2_icon_text=?, f2_icon_url=?, f2_title=?, f2_desc=?, f3_icon_text=?, f3_icon_url=?, f3_title=?, f3_desc=?, f4_icon_text=?, f4_icon_url=?, f4_title=?, f4_desc=? WHERE id=1');
  $stmt->bind_param('ssssssssssssssssssss',
    $header_title, $header_subtext, $center_image_url, $bg_image_url,
    $f[1]['icon_text'], $f[1]['icon_url'], $f[1]['title'], $f[1]['desc'],
    $f[2]['icon_text'], $f[2]['icon_url'], $f[2]['title'], $f[2]['desc'],
    $f[3]['icon_text'], $f[3]['icon_url'], $f[3]['title'], $f[3]['desc'],
    $f[4]['icon_text'], $f[4]['icon_url'], $f[4]['title'], $f[4]['desc']
  );
  $stmt->execute();
  $msg = 'Saved';
  header('Location: /APLX/frontend/admin/why_best.php?msg='.urlencode($msg));
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Why Best Section</title>
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
    fieldset{border:1px solid var(--border);border-radius:10px;padding:10px}
    legend{color:var(--muted)}
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
      <h2>Why we are considered the best in business</h2>
      <?php if ($msg): ?><div class="notice"><?php echo h($msg); ?></div><?php endif; ?>
      <form class="stack" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
        <div class="grid2">
          <div>
            <label>Header Title<input name="header_title" value="<?php echo h($row['header_title'] ?? ''); ?>" placeholder="Why we are considered the best in business"></label>
            <label>Header Subtext<textarea name="header_subtext" rows="3" placeholder="Decentralized trade, direct transport, ..."><?php echo h($row['header_subtext'] ?? ''); ?></textarea></label>
            <label>Upload Center Image<input type="file" name="center_image_file" accept="image/*"></label>
            <label>Or Image URL<input name="center_image_url" placeholder="https://..." value="<?php echo h($row['center_image_url'] ?? ''); ?>"></label>
            <hr>
            <label>Section Background Image (Why Choose Us)</label>
            <label>Upload Background Image<input type="file" name="bg_image_file" accept="image/*"></label>
            <label>Or Background URL<input name="bg_image_url" placeholder="https://..." value="<?php echo h($row['bg_image_url'] ?? ''); ?>"></label>
            <?php if (!empty($row['bg_image_url'])): ?><div class="muted"><img class="image-thumb" src="<?php echo h($row['bg_image_url']); ?>" alt=""></div><?php endif; ?>
          </div>
          <div>
            <?php for($i=1;$i<=4;$i++): ?>
            <fieldset class="stack">
              <legend>Feature #<?php echo $i; ?></legend>
              <label>Icon Emoji<input name="f<?php echo $i; ?>_icon_text" value="<?php echo h($row['f'.$i.'_icon_text'] ?? ''); ?>" placeholder="Emoji"></label>
              <label>Icon Image<input type="file" name="f<?php echo $i; ?>_icon_file" accept="image/*"></label>
              <?php if (!empty($row['f'.$i.'_icon_url'])): ?><div class="muted"><img class="image-thumb" src="<?php echo h($row['f'.$i.'_icon_url']); ?>" alt=""></div><?php endif; ?>
              <label>Title<input name="f<?php echo $i; ?>_title" value="<?php echo h($row['f'.$i.'_title'] ?? ''); ?>" placeholder="Title"></label>
              <label>Description<input name="f<?php echo $i; ?>_desc" value="<?php echo h($row['f'.$i.'_desc'] ?? ''); ?>" placeholder="Short description"></label>
            </fieldset>
            <?php endfor; ?>
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
