<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Notifications</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:48px 0 12px 0}
    .list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px}
    .list li{background:#0b1220;border:1px solid var(--border);border-radius:10px;padding:10px}
    .list .title{font-weight:700}
    .list .meta{color:var(--muted);font-size:12px;margin-top:4px}
    .filters{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .filters input,.filters select{height:32px;padding:6px 10px;border-radius:8px;border:1px solid var(--border)}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>

    <div class="toolbar">
      <h2 style="margin:0">Notifications</h2>
      <form id="notifFilter" class="filters" action="#">
        <input type="text" id="q" placeholder="Search title or message" />
        <select id="limit">
          <option value="20">20</option>
          <option value="50">50</option>
          <option value="100">100</option>
          <option value="all">All</option>
        </select>
        <button class="btn" type="submit">Apply</button>
      </form>
    </div>

    <section class="card">
      <ul id="notifListAll" class="list">
        <li class="muted">Loading...</li>
      </ul>
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
  const list = document.getElementById('notifListAll');
  const form = document.getElementById('notifFilter');
  const q = document.getElementById('q');
  const limitSel = document.getElementById('limit');
  const prevBtn = document.getElementById('prevPg');
  const nextBtn = document.getElementById('nextPg');
  const pgInfo = document.getElementById('pgInfo');
  let page = 1, limit = 20, total = 0, showAll = false;

  function currentLimit(){
    const v = (limitSel?.value||'20');
    showAll = (v === 'all');
    return showAll ? 200 : parseInt(v,10)||20;
  }

  function fmtTimeSL(d){
    try{
      const tz='Asia/Colombo';
      const dt = (typeof d === 'string') ? new Date(d.replace(' ', 'T')) : new Date(d);
      const date = new Intl.DateTimeFormat('en-GB',{timeZone:tz,year:'numeric',month:'short',day:'2-digit'}).format(dt);
      const time = new Intl.DateTimeFormat('en-GB',{timeZone:tz,hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}).format(dt);
      const day  = new Intl.DateTimeFormat('en-GB',{timeZone:tz,weekday:'long'}).format(dt);
      return `${date} • ${time} • ${day}`;
    }catch(_){ return String(d||''); }
  }
  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m])); }

  async function load(){
    if(!list) return;
    list.innerHTML = '<li class="muted">Loading...</li>';
    limit = currentLimit();
    const params = new URLSearchParams({ page:String(page), limit:String(limit) });
    const query = (q?.value||'').trim();
    if (query) params.set('search', query);
    if (showAll) params.set('all','1');
    try{
      const res = await fetch('/APLX/backend/admin/notifications.php?api=1&'+params.toString(), { cache:'no-store' });
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();
      total = Number(data.total||0);
      const items = Array.isArray(data.items) ? data.items : [];
      if(!items.length){ list.innerHTML = '<li class="muted">No notifications</li>'; }
      else{
        list.innerHTML = items.map(n=>{
          const title = escapeHtml(n.title || n.type || 'Notification');
          const msg = escapeHtml(n.message || n.body || '');
          const when = fmtTimeSL(n.created_at || n.time || '');
          return `<li>
            <div class="title">${title}</div>
            <div class="desc">${msg}</div>
            <div class="meta">${when}</div>
          </li>`;
        }).join('');
      }
      const maxPg = showAll ? 1 : Math.max(1, Math.ceil(total/limit));
      if(pgInfo) pgInfo.textContent = showAll ? `${total} rows` : `Page ${page} / ${maxPg} — ${total} messages`;
      if(prevBtn) prevBtn.disabled = showAll || page<=1;
      if(nextBtn) nextBtn.disabled = showAll || page>=maxPg;
    }catch(err){
      list.innerHTML = '<li class="muted">Failed to load notifications</li>';
    }
  }

  form?.addEventListener('submit', (e)=>{ e.preventDefault(); page=1; load(); });
  limitSel?.addEventListener('change', ()=>{ page=1; load(); });
  prevBtn?.addEventListener('click', ()=>{ if(page>1){ page--; load(); } });
  nextBtn?.addEventListener('click', ()=>{ page++; load(); });

  load();
})();
</script>
</body>
</html>


