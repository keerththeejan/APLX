<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();

// Ensure DB schema needed by this page exists
function ensure_services_schema(mysqli $conn){
  try{
    $res = $conn->query("SHOW COLUMNS FROM services LIKE 'icon_url'");
    if ($res && $res->num_rows === 0){
      $conn->query("ALTER TABLE services ADD COLUMN icon_url VARCHAR(512) NULL AFTER icon");
    }
  }catch(Throwable $e){ /* ignore; page will still work without icon */ }
}
ensure_services_schema($conn);

function respond_redirect($url){ header('Location: ' . $url); exit; }
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

$id = intval($_GET['id'] ?? 0);
$msg = '';
$edit = null;
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  csrf_check();
  $mode = $_POST['_mode'] ?? 'create';
  if ($mode === 'delete'){
    $delId = intval($_POST['id'] ?? 0);
    if ($delId > 0){
      $stmt = $conn->prepare('DELETE FROM services WHERE id=?');
      $stmt->bind_param('i', $delId);
      $stmt->execute();
      $msg = 'Service deleted';
    }
    respond_redirect('/APLX/frontend/admin/services.php?msg=' . urlencode($msg));
  }
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $sort_order = intval($_POST['sort_order'] ?? 0);
  $icon_url = save_upload_img('icon_file', 'services/icons');
  $image_url = save_upload_img('image_file', 'services');
  if (!$image_url) { $image_url = trim($_POST['image_url'] ?? ''); }
  if ($mode === 'update'){
    $updId = intval($_POST['id'] ?? 0);
    if ($updId > 0){
      $cur = $conn->prepare('SELECT icon_url, image_url FROM services WHERE id=?');
      $cur->bind_param('i', $updId);
      $cur->execute();
      $old = $cur->get_result()->fetch_assoc() ?: [];
      $icon_final = $icon_url ?: ($old['icon_url'] ?? null);
      $img_final = $image_url ?: ($old['image_url'] ?? '');
      $stmt = $conn->prepare('UPDATE services SET icon_url=?, image_url=?, title=?, description=?, sort_order=? WHERE id=?');
      $stmt->bind_param('ssssii', $icon_final, $img_final, $title, $description, $sort_order, $updId);
      $stmt->execute();
      $msg = 'Service updated';
      respond_redirect('/APLX/frontend/admin/services.php?msg=' . urlencode($msg));
    }
  } else {
    if (!$image_url) { $err = 'Image is required'; }
    if (empty($title) || empty($description)) { $err = ($err? $err.'; ' : '') . 'Title and description required'; }
    if (empty($err)){
      $stmt = $conn->prepare('INSERT INTO services(icon_url, image_url, title, description, sort_order) VALUES (?, ?, ?, ?, ?)');
      $stmt->bind_param('ssssi', $icon_url, $image_url, $title, $description, $sort_order);
      $stmt->execute();
      $msg = 'Service created';
      respond_redirect('/APLX/frontend/admin/services.php?msg=' . urlencode($msg));
    }
  }
}

$services = [];
$res = $conn->query('SELECT * FROM services ORDER BY sort_order, id');
while ($row = $res->fetch_assoc()) { $services[] = $row; }

if ($id > 0){
  $stmt = $conn->prepare('SELECT * FROM services WHERE id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $edit = $stmt->get_result()->fetch_assoc();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Services</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .services-admin{ display:grid;grid-template-columns:2fr 1fr;gap:16px }
    @media (max-width: 1000px){ .services-admin{ grid-template-columns:1fr; } }
    .list table{ width:100%; border-collapse: collapse; }
    .list tr:hover{ background:#0b1220; }
    .actions button{ margin-right:6px; }
    .muted{ color:var(--muted); }
    .preview-icon{ font-size:20px; }
    .image-thumb{ width:64px; height:40px; object-fit:cover; border-radius:6px; border:1px solid var(--border); }
    .page-actions{ text-align:right; margin:8px 0 12px; }
    .page-actions a{ display:inline-block; margin-left:8px; }
    /* Match action button style used elsewhere */
    .btn-icon{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid var(--border);background:#0b1220}
    .btn-blue{background:#1d4ed8;border-color:#1e40af;color:#fff}
    .btn-blue:hover{filter:brightness(1.08)}
    .btn-red{background:#dc2626;border-color:#b91c1c;color:#fff}
    .btn-red:hover{filter:brightness(1.08)}
    .btn-green{background:#16a34a;border-color:#15803d;color:#fff}
    .btn-green:hover{filter:brightness(1.08)}
    /* Dark theme inputs */
    .card input[type="text"], .card input[type="number"], .card input[type="email"], .card input[type="tel"], .card input[type="file"], .card select{
      background:#0b1220; border:1px solid var(--border); color:var(--text); border-radius:8px; padding:10px; width:100%;
    }
    .card textarea{ resize:vertical; }
    .card input::placeholder, .card textarea::placeholder{ color:var(--muted); }
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>
    <div class="page-actions">
      <a class="btn btn-outline" href="/APLX/frontend/admin/settings.php" title="Back to Settings">← Back to Settings</a>
    </div>

    <section class="card">
      <h2 id="pageTitle">Services</h2>
      <?php if (isset($_GET['msg'])): ?><div class="notice" role="status" aria-live="polite"><?php echo h($_GET['msg']); ?></div><?php endif; ?>
      <div class="services-admin">
        <div class="list">
          <table aria-label="Services list">
            <thead>
              <tr>
                <th>#</th>
                <th>Icon</th>
                <th>Image</th>
                <th>Title</th>
                <th>Description</th>
                <th>Order</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($services)): ?>
              <tr><td colspan="7" class="muted">No services. Use the form to add.</td></tr>
            <?php else: foreach($services as $i=>$it): ?>
              <tr>
                <td><?php echo $i+1; ?></td>
                <td><?php if(!empty($it['icon_url'])): ?><img class="image-thumb" src="<?php echo h($it['icon_url']); ?>" alt=""><?php endif; ?></td>
                <td><?php if(!empty($it['image_url'])): ?><img class="image-thumb" src="<?php echo h($it['image_url']); ?>" alt=""><?php endif; ?></td>
                <td><?php echo h($it['title']); ?></td>
                <td><?php echo h($it['description']); ?></td>
                <td><?php echo (int)$it['sort_order']; ?></td>
                <td class="actions" style="display:flex;gap:8px;align-items:center;">
                  <a class="btn-icon btn-blue" href="/APLX/frontend/admin/services.php?id=<?php echo (int)$it['id']; ?>" title="Edit" aria-label="Edit">✏️</a>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Delete this service?');">
                    <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="_mode" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                    <button class="btn-icon btn-red" type="submit" title="Delete" aria-label="Delete">🗑️</button>
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
            <?php if ($edit): ?>
              <input type="hidden" name="_mode" value="update">
              <input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">
            <?php endif; ?>
            <label>Icon (upload)
              <input type="file" name="icon_file" accept="image/*">
            </label>
            <?php if ($edit && !empty($edit['icon_url'])): ?>
            <div class="muted"><img class="image-thumb" src="<?php echo h($edit['icon_url']); ?>" alt=""></div>
            <?php endif; ?>
            <label>Upload Image
              <input type="file" name="image_file" accept="image/*">
            </label>
            <label>Image URL
              <input name="image_url" placeholder="https://..." value="<?php echo h($edit['image_url'] ?? ''); ?>">
            </label>
            <label>Title
              <input name="title" value="<?php echo h($edit['title'] ?? ''); ?>" required>
            </label>
            <label>Description
              <input name="description" placeholder="Short description shown on the homepage cards" value="<?php echo h($edit['description'] ?? ''); ?>" required>
            </label>
            <label>Sort Order
              <input name="sort_order" type="number" value="<?php echo isset($edit['sort_order']) ? (int)$edit['sort_order'] : 0; ?>">
            </label>
            <div class="actions" style="margin-top:10px;display:flex;gap:8px;align-items:center;justify-content:flex-end">
              <?php if ($edit): ?>
                <button class="btn btn-green" type="submit" title="Update" aria-label="Update">Update</button>
                <a class="btn btn-red" href="/APLX/frontend/admin/services.php" title="Cancel" aria-label="Cancel">Cancel</a>
              <?php else: ?>
                <button class="btn btn-green" type="submit" title="Create" aria-label="Create">Create</button>
                <a class="btn btn-red" href="/APLX/frontend/admin/services.php" title="Cancel" aria-label="Cancel">Cancel</a>
              <?php endif; ?>
            </div>
            <small class="muted">Tip: Browse and upload an icon image for the card. Title and Description are short texts; lower sort order appears earlier.</small>
          </form>
        </div>
      </div>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
</body>
</html>
