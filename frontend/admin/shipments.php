<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Shipments</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .searchbox{display:flex;gap:8px;align-items:center;justify-content:flex-end;margin:6px 0}
    .searchbox input{padding:10px 12px;border-radius:10px;border:1px solid var(--border);min-width:60px;background:#fff;color:#111}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
  <div id="topbar"></div>
  <section class="card">
    <h2>Shipments (Live)</h2>
    <div class="searchbox">
      <input id="q" type="search" placeholder="Search by tracking, receiver, city" />
      <button class="btn" id="btnSearch">Search</button>
    </div>
    <div class="table-responsive" style="margin-top:12px">
      <table>
        <thead>
          <tr>
            <th>Tracking</th>
            <th>Receiver</th>
            <th>From</th>
            <th>To</th>
            <th>Status</th>
            <th>Updated</th>
          </tr>
        </thead>
        <tbody id="shipTbody">
          <tr><td colspan="6" class="muted">Loading...</td></tr>
        </tbody>
      </table>
    </div>
    <div class="pager" style="display:flex;gap:8px;align-items:center;justify-content:flex-end;margin-top:10px">
      <button class="btn btn-sm" id="prevPg">Prev</button>
      <span id="pgInfo" class="muted">–</span>
      <button class="btn btn-sm" id="nextPg">Next</button>
    </div>
  </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
<script>
(function(){
  const btnSearch = document.getElementById('btnSearch');
  const input = document.getElementById('q');
  const tbody = document.getElementById('shipTbody');
  const prevPg= document.getElementById('prevPg');
  const nextPg= document.getElementById('nextPg');
  const pgInfo= document.getElementById('pgInfo');
  let page=1, limit=10, total=0;

  async function load(){
    if(!tbody) return;
    tbody.innerHTML = '<tr><td colspan="6" class="muted">Loading...</td></tr>';
    const params = new URLSearchParams({ page:String(page), limit:String(limit), search:(input?.value||'').trim() });
    const res = await fetch('/APLX/backend/router.php?route=admin/shipments&'+params.toString());
    if(!res.ok){ tbody.innerHTML = '<tr><td colspan="6" class="muted">Failed to load</td></tr>'; return; }
    const data = await res.json();
    total = Number(data.total||0);
    const items = data.items||[];
    if(items.length===0){ tbody.innerHTML = '<tr><td colspan="6" class="muted">No results</td></tr>'; }
    else {
      tbody.innerHTML = items.map(s => `
        <tr>
          <td>${escapeHtml(s.tracking_number||'')}</td>
          <td>${escapeHtml(s.receiver_name||'')}</td>
          <td>${escapeHtml(s.origin||'')}</td>
          <td>${escapeHtml(s.destination||'')}</td>
          <td>${escapeHtml(s.status||'')}</td>
          <td>${escapeHtml(s.updated_at||'')}</td>
        </tr>
      `).join('');
    }
    const maxPg = Math.max(1, Math.ceil(total/limit));
    if(pgInfo) pgInfo.textContent = `Page ${page} / ${maxPg} — ${total} rows`;
    if(prevPg) prevPg.disabled = page<=1;
    if(nextPg) nextPg.disabled = page>=maxPg;
  }

  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }

  btnSearch?.addEventListener('click', ()=>{ page=1; load(); });
  input?.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); page=1; load(); }});
  prevPg?.addEventListener('click', ()=>{ if(page>1){ page--; load(); }});
  nextPg?.addEventListener('click', ()=>{ page++; load(); });

  load();
})();
</script>
</body>
</html>
