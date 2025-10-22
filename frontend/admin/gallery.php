<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();

// Helpers
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

// Routing state
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);
$msg = '';
$edit = null;

// Handle POST actions (create/update/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $mode = $_POST['_mode'] ?? 'create';
  if ($mode === 'delete') {
    $delId = intval($_POST['id'] ?? 0);
    if ($delId > 0) {
      $stmt = $conn->prepare('DELETE FROM gallery WHERE id=?');
      $stmt->bind_param('i', $delId);
      $stmt->execute();
      $msg = 'Gallery item deleted';
    }
    respond_redirect('/APLX/frontend/admin/gallery.php?msg=' . urlencode($msg));
  }

  // Create or update (map UI -> DB)
  $tag = trim($_POST['category'] ?? '');
  $day = null; // You can add inputs later; for now keep null
  $month = null;
  $sort_order = intval($_POST['sort_order'] ?? 0);
  $is_active = isset($_POST['is_active']) ? 1 : 0;
  
  // Handle file upload
  $image_url = save_upload_img('image_file', 'gallery');
  if (!$image_url && $mode === 'create') {
    $image_url = trim($_POST['image_url'] ?? '');
  }
  
  if ($mode === 'update') {
    $updateId = intval($_POST['id'] ?? 0);
    if ($updateId > 0) {
      // Keep old image if not re-uploaded
      if (!$image_url) {
        $cur = $conn->prepare('SELECT image_url FROM gallery WHERE id=?');
        $cur->bind_param('i', $updateId);
        $cur->execute();
        $image_url = ($cur->get_result()->fetch_assoc()['image_url'] ?? '');
      }
      $stmt = $conn->prepare('UPDATE gallery SET image_url=?, tag=?, day=?, month=?, sort_order=? WHERE id=?');
      $stmt->bind_param('ssissi', $image_url, $tag, $day, $month, $sort_order, $updateId);
      $stmt->execute();
      $msg = 'Gallery item updated';
      respond_redirect('/APLX/frontend/admin/gallery.php?msg=' . urlencode($msg));
    }
  } else {
    if ($image_url) {
      $stmt = $conn->prepare('INSERT INTO gallery (image_url, tag, day, month, sort_order) VALUES (?, ?, ?, ?, ?)');
      $stmt->bind_param('ssisi', $image_url, $tag, $day, $month, $sort_order);
      $stmt->execute();
      $msg = 'Gallery item added';
      respond_redirect('/APLX/frontend/admin/gallery.php?msg=' . urlencode($msg));
    } else {
      $err = 'Image is required';
    }
  }
}

// Get all gallery items
$gallery_items = [];
$result = $conn->query('SELECT * FROM gallery ORDER BY sort_order, id');
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $gallery_items[] = $row;
  }
}

// Get single item for editing
if ($id > 0) {
  $stmt = $conn->prepare('SELECT * FROM gallery WHERE id = ?');
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
  <title>Admin | Gallery</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .thumb {
      width: 120px;
      height: 75px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid var(--border);
    }
    .actions button {
      margin-right: 6px;
    }
    .badge {
      display: inline-block;
      background: #3b82f6;
      color: white;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 12px;
    }
    .inactive {
      opacity: 0.6;
    }
    /* Match hero_banners action button styles */
    .btn-icon{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid var(--border);background:#0b1220}
    .btn-blue{background:#1d4ed8;border-color:#1e40af;color:#fff}
    .btn-blue:hover{filter:brightness(1.08)}
    .btn-red{background:#dc2626;border-color:#b91c1c;color:#fff}
    .btn-red:hover{filter:brightness(1.08)}
    .btn-green{background:#16a34a;border-color:#15803d;color:#fff}
    .btn-green:hover{filter:brightness(1.08)}
    .actions .btn{min-width:110px;height:40px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px}
    /* Dark theme inputs match hero_banners */
    .card input[type="text"],
    .card input[type="number"],
    .card input[type="file"],
    .card select,
    .card textarea,
    .stack input[type="text"],
    .stack input[type="number"],
    .stack input[type="file"],
    .stack select,
    .stack textarea {
      background:#0b1220;
      border:1px solid var(--border);
      color:var(--text);
      border-radius:8px;
      padding:10px;
      width:100%;
    }
    .card input::placeholder,
    .card textarea::placeholder,
    .stack input::placeholder,
    .stack textarea::placeholder { color: var(--muted); }
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="notice" role="status" aria-live="polite"><?php echo h($_GET['msg']); ?></div>
    <?php endif; ?>

    <section class="card">
      <h2>Gallery</h2>
      
      <div class="grid" style="grid-template-columns:1.2fr .8fr; gap:16px; align-items:start;">
        <div>
          <h3 class="muted">All Gallery Items</h3>
          <table class="table" aria-label="Gallery items list">
            <thead>
              <tr>
                <th style="width:40px">#</th>
                <th style="width:120px">Image</th>
                <th style="width:140px">Date</th>
                <th style="width:120px">Tag</th>
                <th style="width:80px">Order</th>
                <th style="width:140px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($gallery_items)): ?>
                <tr>
                  <td colspan="6" class="muted">No gallery items found. Add one using the form.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($gallery_items as $index => $item): ?>
                  <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><img src="<?php echo h($item['image_url']); ?>" alt="" class="thumb"></td>
                    <td><?php echo ($item['day']?'<span class="badge"><strong>'.h($item['day']).'</strong> '.h($item['month']).'</span>':''); ?></td>
                    <td><span class="badge"><?php echo h($item['tag']); ?></span></td>
                    <td><?php echo (int)$item['sort_order']; ?></td>
                    <td class="actions" style="display:flex;gap:8px;align-items:center;">
                      <a href="?id=<?php echo $item['id']; ?>" class="btn-icon btn-blue" title="Edit" aria-label="Edit">✏️</a>
                      <form method="post" style="display:inline;" onsubmit="return confirm('Delete this image?');">
                        <input type="hidden" name="_mode" value="delete">
                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                        <button type="submit" class="btn-icon btn-red" title="Delete" aria-label="Delete">🗑️</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div>
          <h3 class="muted"><?php echo $edit ? 'Edit Gallery Item' : 'Add New Item'; ?></h3>
          <form method="post" enctype="multipart/form-data" class="stack" aria-label="<?php echo $edit ? 'Edit gallery item form' : 'Add gallery item form'; ?>">
            <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
            <?php if ($edit): ?>
              <input type="hidden" name="_mode" value="update">
              <input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">
            <?php endif; ?>

            <label>
              Tag
              <input type="text" name="category" placeholder="e.g., Transport, Warehouse" value="<?php echo h($edit['tag'] ?? ''); ?>">
            </label>

            <label>
              <?php echo $edit ? 'Replace Image (leave empty to keep current)' : 'Image'; ?>
              <input type="file" name="image_file" accept="image/*" <?php echo !$edit ? 'required' : ''; ?>>
              <?php if ($edit && !empty($edit['image_url'])): ?>
                <div style="margin-top: 8px;">
                  <img src="<?php echo h($edit['image_url']); ?>" alt="Current image" class="thumb">
                </div>
              <?php endif; ?>
            </label>

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
              <label>
                Sort Order
                <input type="number" name="sort_order" placeholder="0" value="<?php echo (int)($edit['sort_order'] ?? 0); ?>">
              </label>
              
              <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="is_active" value="1" <?php echo !isset($edit['is_active']) || $edit['is_active'] ? 'checked' : ''; ?>>
                <span>Active</span>
              </label>
            </div>

            <div class="actions" style="margin-top:10px;display:flex;gap:8px;align-items:center;justify-content:flex-end">
              <?php if ($edit): ?>
                <button class="btn btn-green" type="submit" title="Update" aria-label="Update">Update</button>
                <a class="btn btn-red" href="/APLX/frontend/admin/gallery.php" title="Cancel" aria-label="Cancel">Cancel</a>
              <?php else: ?>
                <button class="btn btn-green" type="submit" title="Create" aria-label="Create">Create</button>
                <a class="btn btn-red" href="/APLX/frontend/admin/gallery.php" title="Cancel" aria-label="Cancel">Cancel</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
</body>
</html>
