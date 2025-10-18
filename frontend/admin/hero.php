<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Homepage Banner</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>

    <section class="card">
      <h2 id="pageTitle">Homepage Banner</h2>
      <form id="heroForm" class="stack" method="post" action="/APLX/backend/router.php?route=admin/hero" enctype="multipart/form-data">
        <input type="hidden" name="csrf" id="csrfField" value="">
        <label>Eyebrow
          <input type="text" name="eyebrow" id="f_eyebrow" placeholder="Safe Transportation & Logistics">
        </label>
        <label>Title
          <input type="text" name="title" id="f_title" placeholder="Adaptable coordinated factors" required>
        </label>
        <label>Subtitle
          <input type="text" name="subtitle" id="f_subtitle" placeholder="Quick Conveyance" required>
        </label>
        <label>Tagline
          <input type="text" name="tagline" id="f_tagline" placeholder="Reliable logistics solutions...">
        </label>
        <div class="grid">
          <label>Primary Button Text
            <input type="text" name="cta1_text" id="f_cta1_text" placeholder="Get Started">
          </label>
          <label>Primary Button Link
            <input type="text" name="cta1_link" id="f_cta1_link" placeholder="/APLX/frontend/login.php">
          </label>
        </div>
        <div class="grid">
          <label>Secondary Button Text
            <input type="text" name="cta2_text" id="f_cta2_text" placeholder="Learn More">
          </label>
          <label>Secondary Button Link
            <input type="text" name="cta2_link" id="f_cta2_link" placeholder="#">
          </label>
        </div>
        <label>Background Image URL
          <input type="text" name="background_url" id="f_bg" placeholder="https://...">
        </label>
        <label>Or Upload Background Image
          <input type="file" name="image_file" id="f_file" accept="image/*">
        </label>
        <img id="bgPreview" alt="Preview" style="max-width:100%;max-height:220px;border:1px solid var(--border);border-radius:10px;display:none" />
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
          <button class="btn" type="submit">Save</button>
          <button class="btn btn-outline" id="reloadBtn" type="button">Reload</button>
          <a class="btn btn-outline" href="/APLX/frontend/index.php">View Site</a>
        </div>
        <div id="heroStatus" class="muted" aria-live="polite" style="margin-top:6px"></div>
      </form>

      <?php
      // Older banners table (from hero_banners)
      $list = [];
      try {
        $res = $conn->query("SELECT id, eyebrow, title, subtitle, image_url, sort_order, is_active FROM hero_banners ORDER BY is_active DESC, sort_order ASC, id ASC");
        while ($row = $res->fetch_assoc()) { $list[] = $row; }
      } catch (Throwable $e) { $list = []; }
      if (!empty($list)):
      ?>
      <div style="margin-top:16px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <h3 class="muted" style="margin:0">Existing Banners</h3>
          <a class="btn btn-secondary" href="/APLX/frontend/admin/hero_banners.php">Manage All</a>
        </div>
        <div role="table" aria-label="Older banners table">
          <div role="rowgroup">
            <div role="row" style="display:grid;grid-template-columns:80px 1fr 80px 90px;gap:10px;padding:6px 0;border-bottom:1px solid var(--border)">
              <div role="columnheader">Image</div>
              <div role="columnheader">Title</div>
              <div role="columnheader">Order</div>
              <div role="columnheader">Active</div>
            </div>
          </div>
          <div role="rowgroup">
            <?php foreach ($list as $it): ?>
              <div role="row" style="display:grid;grid-template-columns:80px 1fr 80px 90px;gap:10px;padding:6px 0;border-bottom:1px dashed var(--border)">
                <div role="cell"><img src="<?php echo h($it['image_url']); ?>" alt="" style="width:76px;height:50px;object-fit:cover;border-radius:6px;border:1px solid var(--border)"></div>
                <div role="cell">
                  <div><strong><?php echo h($it['title'] ?: $it['subtitle']); ?></strong></div>
                  <div class="muted" style="font-size:12px">ID: <?php echo (int)$it['id']; ?> · Eyebrow: <?php echo h($it['eyebrow']); ?> · <a href="/APLX/frontend/admin/hero_banners.php?action=edit&id=<?php echo (int)$it['id']; ?>">Edit</a></div>
                </div>
                <div role="cell"><?php echo (int)$it['sort_order']; ?></div>
                <div role="cell"><?php echo (int)$it['is_active'] ? 'Yes' : 'No'; ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
<script>
(function(){
  const f = {
    csrf: document.getElementById('csrfField'),
    eyebrow: document.getElementById('f_eyebrow'),
    title: document.getElementById('f_title'),
    subtitle: document.getElementById('f_subtitle'),
    tagline: document.getElementById('f_tagline'),
    c1t: document.getElementById('f_cta1_text'),
    c1l: document.getElementById('f_cta1_link'),
    c2t: document.getElementById('f_cta2_text'),
    c2l: document.getElementById('f_cta2_link'),
    bg: document.getElementById('f_bg')
  };
  const form = document.getElementById('heroForm');
  const statusEl = document.getElementById('heroStatus');
  const reloadBtn = document.getElementById('reloadBtn');
  const preview = document.getElementById('bgPreview');
  const fileInput = document.getElementById('f_file');

  async function getCSRF(){
    try{ const r=await fetch('/APLX/backend/router.php?route=admin/hero&action=csrf',{cache:'no-store'}); if(r.ok){ const d=await r.json(); f.csrf.value=d.csrf||''; } }catch(_){ }
  }
  async function load(){
    statusEl.textContent='Loading...';
    try{
      const r = await fetch('/APLX/backend/router.php?route=admin/hero',{cache:'no-store'});
      const d = r.ok ? await r.json() : { item:{} };
      const it = d.item||{};
      f.eyebrow.value = it.eyebrow||'';
      f.title.value = it.title||'';
      f.subtitle.value = it.subtitle||'';
      f.tagline.value = it.tagline||'';
      f.c1t.value = it.cta1_text||'';
      f.c1l.value = it.cta1_link||'';
      f.c2t.value = it.cta2_text||'';
      f.c2l.value = it.cta2_link||'';
      f.bg.value = it.background_url||'';
      statusEl.textContent='';
      if (preview) {
        if (f.bg.value) { preview.src = f.bg.value; preview.style.display=''; } else { preview.style.display='none'; }
      }
    }catch(e){ statusEl.textContent='Load failed'; }
  }

  form.addEventListener('submit', async (e)=>{
    e.preventDefault(); statusEl.textContent='Saving...';
    try{
      const fd = new FormData(form);
      const r = await fetch(form.action, { method:'POST', body: fd });
      if (!r.ok) throw new Error('HTTP '+r.status);
      statusEl.textContent='Saved';
      await getCSRF();
    }catch(e){ statusEl.textContent='Save failed'; }
  });

  reloadBtn.addEventListener('click', load);
  fileInput?.addEventListener('change', ()=>{
    if (fileInput.files && fileInput.files[0]){
      const url = URL.createObjectURL(fileInput.files[0]);
      preview.src = url; preview.style.display='';
    }
  });
  getCSRF().then(load);
})();
</script>
</body>
</html>
