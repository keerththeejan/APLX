<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Help + Quote</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media (max-width: 900px){ .two-col{grid-template-columns:1fr} }
    .card input,.card textarea{background:#0b1220;border:1px solid var(--border);color:var(--text);border-radius:8px;padding:10px;width:100%}
    .card textarea{resize:vertical}
    .muted{color:var(--muted)}
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
      <h2>Help + Quote (Left Panel)</h2>
      <form id="hqForm" class="stack" method="post" action="/APLX/backend/admin/help_quote_api.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf" id="csrfField" value="">
        <div class="two-col">
          <label>Eyebrow
            <input type="text" name="eyebrow" id="f_eyebrow" placeholder="Transport & Logistics Services">
          </label>
          <label>Title
            <input type="text" name="title" id="f_title" placeholder="We are the best">
          </label>
        </div>
        <label>Subtext
          <input type="text" name="subtext" id="f_subtext" placeholder="Short supporting line">
        </label>
        <label>Bullets (one per line)
          <textarea name="bullets_text" id="f_bullets" rows="4" placeholder="Preaching Worship An Online Family"></textarea>
        </label>
        <div class="two-col">
          <label>Mini Title
            <input type="text" name="mini_title" id="f_mini_title" placeholder="Leading global logistic">
          </label>
          <label>Mini Subtext
            <input type="text" name="mini_sub" id="f_mini_sub" placeholder="and transport agency since 1990">
          </label>
        </div>
        <div class="two-col">
          <label>Mini Image (upload)
            <input type="file" name="mini_image" id="f_mini_image" accept="image/*">
          </label>
          <label>Or Mini Image URL
            <input type="text" name="mini_image_url" id="f_mini_url" placeholder="https://...">
          </label>
        </div>
        <div style="display:flex;gap:8px;align-items:center;margin-top:8px">
          <button class="btn" type="submit">Save</button>
          <button class="btn btn-outline" id="btnReload" type="button">Reload</button>
          <a class="btn btn-outline" href="/APLX/frontend/index.php">View Site</a>
        </div>
        <div id="hqStatus" class="muted" aria-live="polite" style="margin-top:6px"></div>
      </form>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
<script>
(function(){
  const form = document.getElementById('hqForm');
  const statusEl = document.getElementById('hqStatus');
  const btnReload = document.getElementById('btnReload');
  const csrfField = document.getElementById('csrfField');
  const f = {
    eyebrow: document.getElementById('f_eyebrow'),
    title: document.getElementById('f_title'),
    subtext: document.getElementById('f_subtext'),
    bullets: document.getElementById('f_bullets'),
    mini_title: document.getElementById('f_mini_title'),
    mini_sub: document.getElementById('f_mini_sub'),
    mini_url: document.getElementById('f_mini_url'),
  };
  async function csrf(){ try{ const r=await fetch('/APLX/backend/admin/help_quote_api.php?action=csrf',{cache:'no-store'}); if(r.ok){ const d=await r.json(); csrfField.value=d.csrf||''; } }catch(e){} }
  async function load(){
    statusEl.textContent='Loading...';
    try{
      const r = await fetch('/APLX/backend/admin/help_quote_api.php',{cache:'no-store'});
      const d = r.ok ? await r.json() : {item:{}};
      const it = d.item||{};
      f.eyebrow.value = it.eyebrow||'';
      f.title.value = it.title||'';
      f.subtext.value = it.subtext||'';
      f.bullets.value = it.bullets_text||'';
      f.mini_title.value = it.mini_title||'';
      f.mini_sub.value = it.mini_sub||'';
      f.mini_url.value = it.mini_image_url||'';
      statusEl.textContent='';
    }catch(e){ statusEl.textContent='Load failed'; }
  }
  form.addEventListener('submit', async (e)=>{
    e.preventDefault(); statusEl.textContent='Saving...';
    try{
      const fd = new FormData(form);
      const r = await fetch(form.action,{ method:'POST', body: fd });
      if (!r.ok) throw new Error('HTTP '+r.status);
      statusEl.textContent='Saved';
      await csrf();
    }catch(e){ statusEl.textContent='Save failed'; }
  });
  btnReload.addEventListener('click', load);
  csrf().then(load);
})();
</script>
</body>
</html>
