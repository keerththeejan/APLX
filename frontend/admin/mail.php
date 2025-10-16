<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Mail</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .content{padding:16px;margin-left:260px}
    .toolbar{display:flex;flex-direction:column;gap:10px;align-items:flex-start;margin:48px 0 12px 0}
    .mc-row{display:flex;justify-content:flex-start}
    .searchbox{display:flex;gap:8px;align-items:center}
    .searchbox input{padding:10px 12px;border-radius:10px;border:1px solid var(--border);min-width:260px;background:#fff;color:#111}
    .controls-right{display:flex;flex-direction:column;align-items:flex-end;width:100%}
    .under-search{margin-top:6px}
    .sm-select{padding:6px 8px !important;font-size:12px !important;min-width:140px;border-radius:8px}
    table.data{width:100%;border-collapse:separate;border-spacing:0 12px}
    table.data th{color:var(--muted);text-align:left;padding:8px 12px;font-weight:600}
    table.data td{background:#0b1220;border:1px solid var(--border);padding:12px;border-left:none;border-right:none}
    table.data tr{border-radius:14px;box-shadow:0 6px 16px rgba(0,0,0,.25)}
    table.data thead th:first-child{padding-left:16px}
    table.data tbody td:first-child{border-top-left-radius:14px;border-bottom-left-radius:14px;padding-left:16px}
    table.data tbody td:last-child{border-top-right-radius:14px;border-bottom-right-radius:14px}
    .badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:600}
    .badge.ok{background:#052e1a;color:#22c55e;box-shadow:inset 0 0 0 1px #14532d}
    .pager{display:flex;gap:8px;align-items:center;justify-content:flex-end;margin-top:10px}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>

    <div class="toolbar">
      <h2>Mail Logs</h2>
      <div class="mc-row"><a class="btn" href="/APLX/frontend/admin/message_customer.php">Message Customer</a></div>
      <div class="controls-right">
        <div class="searchbox">
          <input id="q" type="search" placeholder="Search email or subject">
          <button class="btn" id="btnSearch">Search</button>
        </div>
        <div class="under-search">
          <select id="f_type" class="sm-select">
            <option value="">All</option>
            <option value="admin">Admin</option>
            <option value="customer">Customer</option>
          </select>
        </div>
      </div>
    </div>

    <div class="card">
      <table class="data">
        <thead>
          <tr>
            <th>Type</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody id="logsTbody"></tbody>
      </table>
      <div class="pager">
        <button class="btn btn-sm" id="prevPg">Prev</button>
        <span id="pgInfo" class="muted">–</span>
        <button class="btn btn-sm" id="nextPg">Next</button>
      </div>
    </div>
  </main>
</div>

<script src="/APLX/js/admin.js"></script>
<script>
(function(){
  const tbody = document.getElementById('logsTbody');
  const q = document.getElementById('q');
  const fType = document.getElementById('f_type');
  const btnSearch = document.getElementById('btnSearch');
  const prevPg = document.getElementById('prevPg');
  const nextPg = document.getElementById('nextPg');
  const pgInfo = document.getElementById('pgInfo');
  let page = 1, limit = 12, total = 0;

  async function load(){
    const params = new URLSearchParams({ page, limit });
    const type = (fType.value||'').trim();
    const search = (q.value||'').trim();
    if (type) params.set('type', type);
    if (search) params.set('search', search);
    const res = await fetch('/APLX/backend/admin/mail_logs.php?' + params.toString());
    const data = await res.json();
    total = data.total || 0;
    renderRows(data.items||[]);
    const maxPg = Math.max(1, Math.ceil(total/limit));
    pgInfo.textContent = `Page ${page} / ${maxPg} — ${total} mails`;
    prevPg.disabled = page<=1; nextPg.disabled = page>=maxPg;
  }

  function esc(s){ return String(s||'').replace(/[&<>"']/g, m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[m])); }
  function formatDT(s){
    if (!s) return '';
    const d = new Date(s.replace(' ','T'));
    if (isNaN(d)) return esc(s);
    return d.toLocaleString();
  }
  function badge(status){
    const ok = (String(status).toLowerCase()==='sent');
    return `<span class="badge ${ok?'ok':''}">${esc(status||'')}</span>`;
  }

  function renderRows(items){
    tbody.innerHTML = items.map(r => `
      <tr>
        <td>${esc(r.recipient_type)}</td>
        <td>${esc(r.recipient_email)}</td>
        <td>${esc(r.subject)}</td>
        <td>${badge(r.status)}</td>
        <td>${formatDT(r.created_at)}</td>
      </tr>
    `).join('');
    if (!items.length) {
      tbody.innerHTML = `<tr><td colspan="5" class="muted" style="background:transparent;border:none;padding:8px 12px">No mail logs</td></tr>`;
    }
  }

  btnSearch.addEventListener('click', ()=>{ page=1; load(); });
  q.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); page=1; load(); }});
  fType.addEventListener('change', ()=>{ page=1; load(); });
  prevPg.addEventListener('click', ()=>{ if(page>1){ page--; load(); }});
  nextPg.addEventListener('click', ()=>{ page++; load(); });

  load();
})();
</script>
</body>
</html>




