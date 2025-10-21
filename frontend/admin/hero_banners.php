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
$err = '';

// Handle POST actions (create/update/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $mode = $_POST['_mode'] ?? 'create';
  if ($mode === 'delete') {
    $delId = intval($_POST['id'] ?? 0);
    if ($delId > 0) {
      $stmt = $conn->prepare('DELETE FROM hero_banners WHERE id=?');
      $stmt->bind_param('i', $delId);
      $stmt->execute();
      $msg = 'Banner deleted';
    }
    respond_redirect('/APLX/frontend/admin/hero_banners.php?msg=' . urlencode($msg));
  }

  // Create or update
  $eyebrow = trim($_POST['eyebrow'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $subtitle = trim($_POST['subtitle'] ?? '');
  $tagline = trim($_POST['tagline'] ?? '');
  $c1t = trim($_POST['cta1_text'] ?? '');
  $c1l = trim($_POST['cta1_link'] ?? '');
  $c2t = trim($_POST['cta2_text'] ?? '');
  $c2l = trim($_POST['cta2_link'] ?? '');
  $sort = intval($_POST['sort_order'] ?? 0);
  $active = isset($_POST['is_active']) ? 1 : 0;
  $imgUrl = save_upload_img('image_file', 'hero') ?: trim($_POST['image_url'] ?? '');

  if (!$title && !$subtitle) { $err = 'Title or Subtitle required'; }
  if (!$imgUrl) { $err = $err ? $err . '; image required' : 'Image required'; }

  if (!$err) {
    if ($mode === 'update') {
      $id = intval($_POST['id'] ?? 0);
      if ($id <= 0) { $err = 'Invalid ID'; }
      else {
        $row = $conn->prepare('SELECT image_url FROM hero_banners WHERE id=?');
        $row->bind_param('i', $id);
        $row->execute();
        $cur = $row->get_result()->fetch_assoc();
        $imgFinal = $imgUrl ?: ($cur['image_url'] ?? '');
        $stmt = $conn->prepare('UPDATE hero_banners SET eyebrow=?, title=?, subtitle=?, tagline=?, cta1_text=?, cta1_link=?, cta2_text=?, cta2_link=?, image_url=?, sort_order=?, is_active=? WHERE id=?');
        $stmt->bind_param('ssssssssssii', $eyebrow,$title,$subtitle,$tagline,$c1t,$c1l,$c2t,$c2l,$imgFinal,$sort,$active,$id);
        $stmt->execute();
        respond_redirect('/APLX/frontend/admin/hero_banners.php?msg=' . urlencode('Banner updated'));
      }
    } else {
      $stmt = $conn->prepare('INSERT INTO hero_banners(eyebrow,title,subtitle,tagline,cta1_text,cta1_link,cta2_text,cta2_link,image_url,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
      $stmt->bind_param('ssssssssssi', $eyebrow,$title,$subtitle,$tagline,$c1t,$c1l,$c2t,$c2l,$imgUrl,$sort,$active);
      $stmt->execute();
      respond_redirect('/APLX/frontend/admin/hero_banners.php?msg=' . urlencode('Banner created'));
    }
  }
}

// Load editing item if any
$edit = null;
if ($action === 'edit' && $id > 0) {
  $stmt = $conn->prepare('SELECT * FROM hero_banners WHERE id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $edit = $stmt->get_result()->fetch_assoc();
}

// List items
$items = [];
$res = $conn->query('SELECT * FROM hero_banners ORDER BY is_active DESC, sort_order ASC, id ASC');
while ($row = $res->fetch_assoc()) { $items[] = $row; }

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Hero Banners</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .table{width:100%;border-collapse:separate;border-spacing:0 6px}
    .table th,.table td{padding:10px;text-align:left}
    .row{display:grid;grid-template-columns:140px 1fr;gap:10px;margin-bottom:10px}
    .actions{display:flex;gap:8px}
    .muted{color:var(--muted)}
    .preview{width:100%;max-width:420px;border:1px solid var(--border);border-radius:10px}
    .btn-icon{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid var(--border);background:#0b1220;}
    .btn-blue{background:#1d4ed8;border-color:#1e40af;color:#fff}
    .btn-blue:hover{filter:brightness(1.08)}
    .btn-red{background:#dc2626;border-color:#b91c1c;color:#fff}
    .btn-red:hover{filter:brightness(1.08)}
    .btn-green{background:#16a34a;border-color:#15803d;color:#fff}
    .btn-green:hover{filter:brightness(1.08)}
    /* Make action buttons same size on this page */
    .actions .btn{min-width:110px;height:40px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>

    <section class="card">
      <h2>Hero Banners</h2>
      <?php if (isset($_GET['msg'])): ?><div class="notice" role="status" aria-live="polite"><?php echo h($_GET['msg']); ?></div><?php endif; ?>

      <div class="grid" style="grid-template-columns:1.2fr .8fr; gap:16px; align-items:start;">
        <div>
          <h3 class="muted">All Banners</h3>
          <div role="table" aria-label="Hero banners list">
            <div class="table-head" role="rowgroup">
              <div role="row" class="row" style="grid-template-columns:60px 1fr 80px 120px;">
                <div role="columnheader">Img</div>
                <div role="columnheader">Title</div>
                <div role="columnheader">Order</div>
                <div role="columnheader">Actions</div>
              </div>
            </div>
            <div class="table-body" role="rowgroup">
              <?php foreach ($items as $it): ?>
                <div role="row" class="row" style="grid-template-columns:60px 1fr 80px 120px; align-items:center;">
                  <div role="cell"><img src="<?php echo h($it['image_url']); ?>" alt="" style="width:56px;height:38px;object-fit:cover;border-radius:6px;border:1px solid var(--border)"></div>
                  <div role="cell">
                    <div><strong><?php echo h($it['title'] ?: $it['subtitle']); ?></strong> <?php if(!$it['is_active']): ?><span class="muted">(inactive)</span><?php endif; ?></div>
                    <div class="muted" style="font-size:12px;">ID: <?php echo (int)$it['id']; ?> · Eyebrow: <?php echo h($it['eyebrow']); ?></div>
                  </div>
                  <div role="cell"><?php echo (int)$it['sort_order']; ?></div>
                  <div role="cell" class="actions">
                    <a class="btn-icon btn-blue" title="Edit" aria-label="Edit" href="/APLX/frontend/admin/hero_banners.php?action=edit&id=<?php echo (int)$it['id']; ?>">✏️</a>
                    <form method="post" onsubmit="return confirm('Delete this banner?');" style="display:inline">
                      <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
                      <input type="hidden" name="_mode" value="delete">
                      <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                      <button class="btn-icon btn-red" type="submit" title="Delete" aria-label="Delete">🗑️</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div>
          <h3 class="muted"><?php echo $edit ? 'Edit Banner' : 'Add Banner'; ?></h3>
          <form method="post" enctype="multipart/form-data" aria-label="<?php echo $edit ? 'Edit banner form' : 'Add banner form'; ?>">
            <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
            <?php if ($edit): ?><input type="hidden" name="_mode" value="update"><input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>"><?php else: ?><input type="hidden" name="_mode" value="create"><?php endif; ?>

            <label class="row"><span>Eyebrow</span><input type="text" name="eyebrow" value="<?php echo h($edit['eyebrow'] ?? ''); ?>" aria-label="Eyebrow"></label>
            <label class="row"><span>Title</span><input type="text" name="title" value="<?php echo h($edit['title'] ?? ''); ?>" aria-label="Title"></label>
            <label class="row"><span>Subtitle</span><input type="text" name="subtitle" value="<?php echo h($edit['subtitle'] ?? ''); ?>" aria-label="Subtitle"></label>
            <label class="row"><span>Tagline</span><input type="text" name="tagline" value="<?php echo h($edit['tagline'] ?? ''); ?>" aria-label="Tagline"></label>

            <label class="row"><span>CTA1 Text</span><input type="text" name="cta1_text" value="<?php echo h($edit['cta1_text'] ?? ''); ?>" aria-label="Primary button text"></label>
            <label class="row"><span>CTA1 Link</span><input type="text" name="cta1_link" value="<?php echo h($edit['cta1_link'] ?? ''); ?>" aria-label="Primary button link"></label>
            <label class="row"><span>CTA2 Text</span><input type="text" name="cta2_text" value="<?php echo h($edit['cta2_text'] ?? ''); ?>" aria-label="Secondary button text"></label>
            <label class="row"><span>CTA2 Link</span><input type="text" name="cta2_link" value="<?php echo h($edit['cta2_link'] ?? ''); ?>" aria-label="Secondary button link"></label>

            <label class="row"><span>Image URL</span><input type="text" name="image_url" value="<?php echo h($edit['image_url'] ?? ''); ?>" aria-label="Image URL"></label>
            <label class="row"><span>Upload Image</span><input type="file" name="image_file" accept="image/*" aria-label="Upload image"></label>
            <label class="row"><span>Sort Order</span><input type="number" name="sort_order" value="<?php echo isset($edit['sort_order']) ? (int)$edit['sort_order'] : 0; ?>" aria-label="Sort order"></label>
            <label class="row"><span>Active</span><input type="checkbox" name="is_active" value="1" <?php echo !isset($edit['is_active']) || (int)$edit['is_active'] ? 'checked' : ''; ?> aria-label="Is active"></label>

            <div class="actions" style="margin-top:10px;display:flex;gap:8px;align-items:center;justify-content:flex-end">
              <?php if ($edit): ?>
                <button class="btn btn-green" type="submit" title="Update" aria-label="Update">Update</button>
              <?php else: ?>
                <button class="btn btn-green" type="submit" title="Create" aria-label="Create">Create</button>
              <?php endif; ?>
              <a class="btn btn-red" href="/APLX/frontend/admin/hero_banners.php" title="Cancel" aria-label="Cancel">Cancel</a>
            </div>
          </form>
          <?php if (($edit['image_url'] ?? '') !== ''): ?>
          <div style="margin-top:10px">
            <img class="preview" src="<?php echo h($edit['image_url']); ?>" alt="Current banner image">
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>
</div>
<script>
// Load admin chrome
fetch('/APLX/frontend/admin/sidebar.php').then(r=>r.text()).then(html=>{ document.getElementById('sidebar').outerHTML = html; });
fetch('/APLX/frontend/admin/topbar.php').then(r=>r.text()).then(html=>{ document.getElementById('topbar').outerHTML = html; });
</script>
</body>
</html>
