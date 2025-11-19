<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();

// Ensure schema exists
$conn->query("CREATE TABLE IF NOT EXISTS service_tabs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  icon_text VARCHAR(16) NULL,
  icon_url VARCHAR(512) NULL,
  image_url VARCHAR(600) NOT NULL DEFAULT '',
  bullets_json TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_active_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS services_tabs_settings (
  id INT PRIMARY KEY,
  bg_image_url VARCHAR(600) NOT NULL DEFAULT '',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
try { $conn->query("INSERT INTO services_tabs_settings (id, bg_image_url) VALUES (1,'') ON DUPLICATE KEY UPDATE bg_image_url=bg_image_url"); } catch (Throwable $e) { }

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

// Delete a previously uploaded background file if it is within our uploads directory
function delete_local_bg($url){
  $url = (string)$url;
  if (!$url) return false;
  // Only allow deletion inside our uploads/services_tabs path
  $prefix = '/APLX/uploads/services_tabs';
  if (strpos($url, $prefix) !== 0) return false;
  $root = realpath(__DIR__ . '/../../');
  $path = $root . str_replace('/APLX', '', $url); // map to filesystem
  if ($path && file_exists($path)) { @unlink($path); return true; }
  return false;
}

$id = intval($_GET['id'] ?? 0);
$msg = '';
$err = '';
$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  csrf_check();
  $mode = $_POST['_mode'] ?? 'create';
  if ($mode === 'save_bg'){
    $bg_up = save_upload_img('bg_file', 'services_tabs');
    $bg = $bg_up ?: trim($_POST['bg_image_url'] ?? '');
    if ($bg){
      // Load current to remove if needed
      $cur = null;
      try { if ($r=$conn->query("SELECT bg_image_url FROM services_tabs_settings WHERE id=1")){ $row=$r->fetch_assoc(); if($row){ $cur=$row['bg_image_url'] ?? ''; } } } catch (Throwable $e) { }
      if (!empty($bg_up) && !empty($cur)) { delete_local_bg($cur); }
      if (empty($bg_up) && !empty($cur) && $bg !== $cur) { // replacing with a new URL; delete old if local
        delete_local_bg($cur);
      }
      $st = $conn->prepare('UPDATE services_tabs_settings SET bg_image_url=? WHERE id=1');
      $st->bind_param('s', $bg);
      $st->execute();
      $msg = 'Background saved';
      header('Location: /APLX/frontend/admin/service_tabs.php?msg='.urlencode($msg));
      exit;
    } else {
      $err = 'Image required';
    }
  }
  if ($mode === 'clear_bg'){
    // Delete current local file if any
    try { if ($r=$conn->query("SELECT bg_image_url FROM services_tabs_settings WHERE id=1")){ $row=$r->fetch_assoc(); if($row && !empty($row['bg_image_url'])){ delete_local_bg($row['bg_image_url']); } } } catch (Throwable $e) { }
    $empty = '';
    $st = $conn->prepare('UPDATE services_tabs_settings SET bg_image_url=? WHERE id=1');
    $st->bind_param('s', $empty);
    $st->execute();
    $msg = 'Background removed';
    header('Location: /APLX/frontend/admin/service_tabs.php?msg='.urlencode($msg));
    exit;
  }
  if ($mode === 'delete'){
    $delId = intval($_POST['id'] ?? 0);
    if ($delId > 0){
      $stmt = $conn->prepare('DELETE FROM service_tabs WHERE id=?');
      $stmt->bind_param('i', $delId);
      $stmt->execute();
      $msg = 'Deleted';
    }
    header('Location: /APLX/frontend/admin/service_tabs.php?msg='.urlencode($msg));
    exit;
  }
  $title = trim($_POST['title'] ?? '');
  $icon_text = trim($_POST['icon_text'] ?? '');
  $icon_url_up = save_upload_img('icon_file', 'service_tabs/icons');
  $image_url_up = save_upload_img('image_file', 'service_tabs');
  $image_url = $image_url_up ?: trim($_POST['image_url'] ?? '');
  $bullets_raw = trim($_POST['bullets'] ?? '');
  $bullets = array_values(array_filter(array_map('trim', preg_split('/\r?\n|,/', $bullets_raw))));
  $bullets_json = json_encode($bullets, JSON_UNESCAPED_UNICODE);

  if (!$title) $err = 'Title required';
  if (!$image_url) $err = ($err? $err.'; ' : '') . 'Image required';

  if (!$err){
    if ($mode === 'update'){
      $updId = intval($_POST['id'] ?? 0);
      if ($updId > 0){
        // Load old icon_url if not replaced
        $cur = $conn->prepare('SELECT icon_url FROM service_tabs WHERE id=?');
        $cur->bind_param('i', $updId);
        $cur->execute();
        $old = $cur->get_result()->fetch_assoc() ?: [];
        $icon_final = $icon_url_up ?: ($old['icon_url'] ?? null);
        $stmt = $conn->prepare('UPDATE service_tabs SET title=?, icon_text=?, icon_url=?, image_url=?, bullets_json=? WHERE id=?');
        $stmt->bind_param('sssssi', $title, $icon_text, $icon_final, $image_url, $bullets_json, $updId);
        $stmt->execute();
        $msg = 'Updated';
      }
    } else {
      $stmt = $conn->prepare('INSERT INTO service_tabs (title, icon_text, icon_url, image_url, bullets_json) VALUES (?,?,?,?,?)');
      $stmt->bind_param('sssss', $title, $icon_text, $icon_url_up, $image_url, $bullets_json);
      $stmt->execute();
      $msg = 'Created';
    }
    header('Location: /APLX/frontend/admin/service_tabs.php?msg='.urlencode($msg));
    exit;
  }
}

if ($id > 0){
  $st = $conn->prepare('SELECT * FROM service_tabs WHERE id=?');
  $st->bind_param('i', $id);
  $st->execute();
  $edit = $st->get_result()->fetch_assoc();
}
$rows = [];
$res = $conn->query('SELECT * FROM service_tabs ORDER BY id ASC');
while($r=$res->fetch_assoc()){ $rows[]=$r; }

$stSetting = ['bg_image_url' => ''];
try { if ($r=$conn->query('SELECT bg_image_url FROM services_tabs_settings WHERE id=1')){ $row=$r->fetch_assoc(); if($row){ $stSetting=$row; } } } catch (Throwable $e) { }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Service Tabs</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .services-admin{ display:grid;grid-template-columns:2fr 1fr;gap:16px }
    @media (max-width: 1000px){ .services-admin{ grid-template-columns:1fr; } }
    .image-thumb{ width:64px; height:40px; object-fit:cover; border-radius:6px; border:1px solid var(--border); }
    /* Dark theme inputs and placeholders */
    .card input[type="text"], .card input[type="number"], .card input[type="email"], .card input[type="tel"], .card input[type="file"], .card select, .card textarea{
      background:#0b1220; border:1px solid var(--border); color:var(--text); border-radius:8px; padding:10px; width:100%; color-scheme: dark;
    }
    /* Placeholder color (cross-browser) */
    .card input::placeholder, .card textarea::placeholder{ color:var(--muted); opacity:1; }
    .card input::-webkit-input-placeholder, .card textarea::-webkit-input-placeholder{ color:var(--muted); opacity:1; }
    .card input::-moz-placeholder, .card textarea::-moz-placeholder{ color:var(--muted); opacity:1; }
    .card input:-ms-input-placeholder, .card textarea:-ms-input-placeholder{ color:var(--muted); opacity:1; }
    .card input::-ms-input-placeholder, .card textarea::-ms-input-placeholder{ color:var(--muted); opacity:1; }
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
      <h2>Service Tabs</h2>
      <?php if (isset($_GET['msg'])): ?><div class="notice"><?php echo h($_GET['msg']); ?></div><?php endif; ?>
      <?php if ($err): ?><div class="error"><?php echo h($err); ?></div><?php endif; ?>
      <div class="services-admin" style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
        <div class="list">
          <table>
            <thead><tr><th>#</th><th>Title</th><th>Icon</th><th>Image</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="muted">No items</td></tr>
              <?php else: foreach($rows as $i=>$it): ?>
                <tr>
                  <td><?php echo $i+1; ?></td>
                  <td><?php echo h($it['title']); ?></td>
                  <td>
                    <?php if (!empty($it['icon_url']) || !empty($it['icon_text'])): ?>
                      <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #333; border: 1px solid #ddd;">
                          <?php if (!empty($it['icon_url'])): ?>
                            <img src="<?php echo h($it['icon_url']); ?>" alt="" style="width: 24px; height: 24px; object-fit: contain;">
                          <?php else: ?>
                            <?php echo h($it['icon_text']); ?>
                          <?php endif; ?>
                        </div>
                        <?php if (!empty($it['icon_text']) && !empty($it['icon_url'])): ?>
                          <span><?php echo h($it['icon_text']); ?></span>
                        <?php endif; ?>
                      </div>
                    <?php else: ?>
                      <div style="width: 36px; height: 36px; border-radius: 50%; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #999;">
                        <span>?</span>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td><?php if(!empty($it['image_url'])): ?><img class="image-thumb" src="<?php echo h($it['image_url']); ?>" alt="" style="width:64px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--border)"><?php endif; ?></td>
                  <td style="display:flex;gap:8px;align-items:center;">
                    <a href="/APLX/frontend/admin/service_tabs.php?id=<?php echo (int)$it['id']; ?>" class="btn-icon" style="background-color: #3b82f6; color: white; border: 1px solid #2563eb; padding: 6px 10px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px;" title="Edit">
                      <span style="font-size: 16px;">✏️</span>
                    </a>
                    <form method="post" onsubmit="return confirm('Delete this item?');" style="margin: 0;">
                      <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                      <input type="hidden" name="_mode" value="delete">
                      <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                      <button type="submit" class="btn-icon" style="background-color: #ef4444; color: white; border: 1px solid #dc2626; padding: 6px 10px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; cursor: pointer;" title="Delete">
                        <span style="font-size: 16px;">🗑️</span>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <div>
          <form class="stack" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
            <?php if ($edit): ?><input type="hidden" name="_mode" value="update"><input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>"><?php endif; ?>
            <label>Title<input name="title" value="<?php echo h($edit['title'] ?? ''); ?>" required></label>
            <label>Icon Emoji (optional)<input name="icon_text" placeholder="e.g. 🛫" value="<?php echo h($edit['icon_text'] ?? ''); ?>"></label>
            <label>Icon Image (optional)<input type="file" name="icon_file" accept="image/*"></label>
            <?php if ($edit && !empty($edit['icon_url'])): ?><div class="muted"><img class="image-thumb" src="<?php echo h($edit['icon_url']); ?>" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;border:1px solid var(--border)"></div><?php endif; ?>
            <label>Upload Main Image<input type="file" name="image_file" accept="image/*"></label>
            <label>Or Image URL<input name="image_url" placeholder="https://..." value="<?php echo h($edit['image_url'] ?? ''); ?>"></label>
            <label>Bullets (one per line)<textarea name="bullets" rows="5" placeholder="Fast Delivery\nSafety\nGood Package\nPrivacy"><?php echo isset($edit['bullets_json']) ? h(implode("\n", (array)json_decode($edit['bullets_json'], true))) : ''; ?></textarea></label>
            <div class="actions" style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
              <?php if ($edit): ?>
                <button class="btn btn-green" type="submit">Update</button>
                <a class="btn btn-red" href="/APLX/frontend/admin/service_tabs.php" style="background-color: #ef4444; color: white; border: 1px solid #dc2626;">Cancel</a>
              <?php else: ?>
                <button class="btn btn-green" type="submit">Create</button>
                <a class="btn btn-red" href="/APLX/frontend/admin/service_tabs.php" style="background-color: #ef4444; color: white; border: 1px solid #dc2626;">Cancel</a>
              <?php endif; ?>
            </div>
          </form>
          <hr style="margin:14px 0; border:0; border-top:1px solid var(--border)">
          <form class="stack" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="_mode" value="save_bg">
            <label>Section Background Image URL<input type="text" name="bg_image_url" placeholder="https://..." value="<?php echo h($stSetting['bg_image_url'] ?? ''); ?>"></label>
            <label>Or Upload Background Image<input type="file" name="bg_file" accept="image/*"></label>
            <?php if (!empty($stSetting['bg_image_url'])): ?>
              <img src="<?php echo h($stSetting['bg_image_url']); ?>" alt="Preview" style="max-width:100%;max-height:180px;border:1px solid var(--border);border-radius:8px;display:block">
            <?php endif; ?>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
              <button class="btn" type="submit">Save Background</button>
              <a class="btn btn-outline" href="/APLX/frontend/index.php" target="_blank">View Site</a>
            </div>
          </form>
          <form method="post" onsubmit="return confirm('Remove background image?');" style="margin-top:8px; display:flex; justify-content:flex-end; gap:8px;">
            <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="_mode" value="clear_bg">
            <button class="btn btn-outline" type="submit" style="border-color:#ef4444;color:#ef4444">Remove Background</button>
          </form>
        </div>
      </div>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
</body>
</html>
