<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Gallery</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .gallery-admin{display:grid;grid-template-columns:2fr 1fr;gap:16px}
    @media (max-width: 1000px){ .gallery-admin{ grid-template-columns:1fr; } }
    table{width:100%;border-collapse:collapse}
    tr:hover{background:#0b1220}
    .thumb{width:120px;height:75px;object-fit:cover;border-radius:8px;border:1px solid var(--border)}
    .actions button{margin-right:6px}
    .muted{color:var(--muted)}
    .badge{display:inline-block;background:#ef4444;color:#fff;padding:2px 6px;border-radius:999px;font-size:12px}
    .page-actions{ text-align:right; margin:8px 0 12px; }
    .page-actions a{ display:inline-block; margin-left:8px; }
    /* Dark theme inputs */
    .card input[type="text"], .card input[type="number"], .card input[type="email"], .card input[type="tel"], .card input[type="file"], .card select{
      background:#0b1220; border:1px solid var(--border); color:var(--text);
      border-radius:8px; padding:10px; width:100%;
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
      <h2 id="pageTitle">Gallery</h2>
      <div class="gallery-admin">
        <div>
          <table aria-label="Gallery list">
            <thead>
              <tr>
                <th>#</th>
                <th>Image</th>
                <th>Date</th>
                <th>Tag</th>
                <th>Order</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="galRows"><tr><td colspan="6" class="muted">Loading...</td></tr></tbody>
          </table>
        </div>
        <div>
          <form id="galForm" class="stack" method="post" action="/APLX/backend/router.php?route=admin/gallery" enctype="multipart/form-data">
            <input type="hidden" name="csrf" id="csrfField" value="">
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="id" id="idField" value="">
            <label>Upload Image
              <input type="file" name="image_file" id="fileField" accept="image/*">
            </label>
            <label>Image URL
              <input name="image_url" id="imgField" placeholder="https://...">
            </label>
            <label>Tag (e.g., Transport, Warehouse)
              <input name="tag" id="tagField" placeholder="Transport">
            </label>
            <div class="grid">
              <label>Day
                <input name="day" id="dayField" type="number" min="1" max="31" placeholder="25">
              </label>
              <label>Month
                <input name="month" id="monthField" placeholder="Dec">
              </label>
            </div>
            <label>Sort Order
              <input name="sort_order" id="orderField" type="number" value="0">
            </label>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <button class="btn" type="submit">Save</button>
              <button class="btn btn-outline" id="resetBtn" type="button">Reset</button>
            </div>
            <div id="formStatus" class="muted" aria-live="polite"></div>
          </form>
        </div>
      </div>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
<script>
(async function(){
  const rows = document.getElementById('galRows');
  const form = document.getElementById('galForm');
  const statusEl = document.getElementById('formStatus');
  const csrfField = document.getElementById('csrfField');
  const methodField = document.getElementById('methodField');
  const idField = document.getElementById('idField');
  const imgField = document.getElementById('imgField');
  const fileField = document.getElementById('fileField');
  const tagField = document.getElementById('tagField');
  const dayField = document.getElementById('dayField');
  const monthField = document.getElementById('monthField');
  const orderField = document.getElementById('orderField');
  const resetBtn = document.getElementById('resetBtn');

  async function getCSRF(){
    try{ const r = await fetch('/APLX/backend/router.php?route=admin/gallery&action=csrf', {cache:'no-store'}); if(r.ok){ const d = await r.json(); csrfField.value = d.csrf || ''; } }catch(_){ }
  }
  await getCSRF();

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }

  async function load(){
    rows.innerHTML = '<tr><td colspan="6" class="muted">Loading...</td></tr>';
    const res = await fetch('/APLX/backend/router.php?route=admin/gallery', { cache:'no-store' });
    const data = res.ok ? await res.json() : { items: [] };
    const items = Array.isArray(data.items) ? data.items : [];
    if (!items.length){ rows.innerHTML = '<tr><td colspan="6" class="muted">No images. Use the form to add.</td></tr>'; return; }
    rows.innerHTML = items.map((it,i)=>{
      const date = (it.day?('<span class="badge"><strong>'+escapeHtml(it.day)+'</strong> '+escapeHtml(it.month||'')+'</span>'):'');
      const tag = it.tag ? ('<span class="badge" style="background:#111827">'+escapeHtml(it.tag)+'</span>') : '';
      return `<tr>
        <td>${i+1}</td>
        <td><img class="thumb" src="${escapeHtml(it.image_url)}" alt=""></td>
        <td>${date}</td>
        <td>${tag}</td>
        <td>${it.sort_order|0}</td>
        <td class="actions">
          <button class="btn btn-small" data-act="edit" data-id="${it.id}">Edit</button>
          <button class="btn btn-small btn-danger" data-act="del" data-id="${it.id}">Delete</button>
        </td>
      </tr>`;
    }).join('');
  }

  function resetForm(){
    methodField.value = 'POST'; idField.value=''; imgField.value=''; if (fileField) fileField.value=''; tagField.value=''; dayField.value=''; monthField.value=''; orderField.value='0'; statusEl.textContent='';
  }
  resetBtn.addEventListener('click', resetForm);

  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button[data-act]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    if (btn.dataset.act === 'edit'){
      const tr = btn.closest('tr');
      idField.value = id; methodField.value='PATCH';
      imgField.value = tr.querySelector('img.thumb')?.getAttribute('src') || '';
      const b = tr.children[2].querySelector('.badge strong');
      dayField.value = b ? b.textContent.trim() : '';
      const m = tr.children[2].querySelector('.badge');
      monthField.value = m ? (m.textContent.replace(/\d+/,'').trim()) : '';
      const t = tr.children[3].querySelector('.badge');
      tagField.value = t ? t.textContent.trim() : '';
      orderField.value = parseInt(tr.children[4].textContent.trim()||'0',10);
      window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 20, behavior:'smooth' });
    } else if (btn.dataset.act === 'del'){
      if (!confirm('Delete this image?')) return;
      const fd = new FormData(); fd.append('csrf', csrfField.value); fd.append('_method','DELETE');
      const res = await fetch(`/APLX/backend/router.php?route=admin/gallery&id=${encodeURIComponent(id)}`, { method:'POST', body: fd });
      if (res.ok){ await load(); statusEl.textContent='Deleted'; } else { statusEl.textContent='Delete failed'; }
    }
  });

  form.addEventListener('submit', async (e)=>{
    e.preventDefault(); statusEl.textContent='Saving...';
    const fd = new FormData(form);
    const res = await fetch(form.action + (methodField.value==='PATCH'?`?id=${encodeURIComponent(idField.value)}`:''), { method:'POST', body: fd });
    if (res.ok){ await load(); statusEl.textContent='Saved'; resetForm(); await getCSRF(); } else { statusEl.textContent='Save failed'; }
  });

  load();
})();
</script>
</body>
</html>
