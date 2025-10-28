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
    .bar-row{display:flex;justify-content:space-between;align-items:center;width:100%}
    .mc-row{display:flex;justify-content:flex-start;align-items:center}
    .searchbox{display:flex;gap:8px;align-items:center}
    .searchbox input{padding:10px 12px;border-radius:10px;border:1px solid var(--border);min-width:260px;background:#fff;color:#111;height:40px}
    .toolbar .btn{height:40px;display:inline-flex;align-items:center}
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
    /* Icon button styles to match customers.php */
    .btn-icon{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:4px;border:none;cursor:pointer;font-size:16px}
    .btn-blue{background:#3b82f6;color:#fff}
    .btn-red{background:#ef4444;color:#fff}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>

    <div class="toolbar">
      <h2>Mail Logs</h2>
      <div class="bar-row">
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
            <th>Action</th>
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

<!-- Edit Modal (subject/status) -->
<div id="mailModal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-panel" style="max-width:520px">
    <div class="modal-header">
      <h3 class="modal-title" id="mailModalTitle">Edit Mail</h3>
      <button class="modal-close" id="mailModalClose" type="button" aria-label="Close">✕</button>
    </div>
    <div class="modal-body">
      <form id="mailForm" class="form-grid">
        <input type="hidden" id="m_id" />
        <div class="form-row">
          <input type="text" id="m_subject" placeholder="Subject" required />
        </div>
        <div class="form-row two">
          <select id="m_status">
            <option value="sent">sent</option>
            <option value="failed">failed</option>
            <option value="queued">queued</option>
          </select>
          <div></div>
        </div>
        <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end">
          <button type="submit" class="btn" id="mailSaveBtn">Save</button>
          <button type="button" class="btn btn-danger" id="mailCancel">Cancel</button>
        </div>
      </form>
      <div id="mailStatus" class="inline-status" aria-live="polite"></div>
    </div>
  </div>
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
        <td>
          <div class="actions">
            <button class="btn-icon btn-blue action-edit" data-id="${r.id}" data-subject="${esc(r.subject)}" data-status="${esc(r.status)}" title="Edit">✏️</button>
            <button class="btn-icon btn-red action-del" data-id="${r.id}" title="Delete">🗑️</button>
          </div>
        </td>
      </tr>
    `).join('');
    if (!items.length) {
      tbody.innerHTML = `<tr><td colspan="6" class="muted" style="background:transparent;border:none;padding:8px 12px">No mail logs</td></tr>`;
    }
  }

  btnSearch.addEventListener('click', ()=>{ page=1; load(); });
  q.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); page=1; load(); }});
  fType.addEventListener('change', ()=>{ page=1; load(); });
  prevPg.addEventListener('click', ()=>{ if(page>1){ page--; load(); }});
  nextPg.addEventListener('click', ()=>{ page++; load(); });

  // Modal helpers (match customers.php behavior)
  const modal = document.getElementById('mailModal');
  const closeBtn = document.getElementById('mailModalClose');
  const cancelBtn = document.getElementById('mailCancel');
  const form = document.getElementById('mailForm');
  const statusEl = document.getElementById('mailStatus');
  const f = {
    id: document.getElementById('m_id'),
    subject: document.getElementById('m_subject'),
    status: document.getElementById('m_status')
  };
  function openModal(){ modal.classList.add('open'); modal.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
  function closeModal(){ modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
  closeBtn.addEventListener('click', closeModal);
  cancelBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e)=>{ if(e.target===modal) closeModal(); });

  // Reusable confirm modal (centered, dark theme)
  const cfm = document.createElement('div');
  cfm.id = 'confirmModal';
  cfm.className = 'modal-backdrop';
  cfm.setAttribute('aria-hidden','true');
  cfm.setAttribute('role','dialog');
  cfm.setAttribute('aria-modal','true');
  cfm.innerHTML = `
    <div class="modal-panel" style="max-width:420px">
      <div class="modal-header">
        <h3 class="modal-title">Confirm</h3>
        <button class="modal-close" id="confirmClose" type="button" aria-label="Close">✕</button>
      </div>
      <div class="modal-body">
        <div id="confirmMsg" style="margin-bottom:12px"></div>
        <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end">
          <button type="button" class="btn" id="confirmOk">OK</button>
          <button type="button" class="btn btn-danger" id="confirmCancel">Cancel</button>
        </div>
      </div>
    </div>`;
  document.body.appendChild(cfm);
  const confirmMsgEl = cfm.querySelector('#confirmMsg');
  const confirmOkBtn = cfm.querySelector('#confirmOk');
  const confirmCancelBtn = cfm.querySelector('#confirmCancel');
  const confirmCloseBtn = cfm.querySelector('#confirmClose');
  function openConfirm(){ cfm.classList.add('open'); cfm.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
  function closeConfirm(){ cfm.classList.remove('open'); cfm.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
  function showConfirm(message){
    confirmMsgEl.textContent = message || 'Are you sure?';
    openConfirm();
    return new Promise((resolve)=>{
      function cleanup(){
        confirmOkBtn.removeEventListener('click', ok);
        confirmCancelBtn.removeEventListener('click', cancel);
        confirmCloseBtn.removeEventListener('click', cancel);
        cfm.removeEventListener('click', onBackdrop);
      }
      function ok(){ cleanup(); closeConfirm(); resolve(true); }
      function cancel(){ cleanup(); closeConfirm(); resolve(false); }
      function onBackdrop(e){ if(e.target===cfm){ cancel(); } }
      confirmOkBtn.addEventListener('click', ok);
      confirmCancelBtn.addEventListener('click', cancel);
      confirmCloseBtn.addEventListener('click', cancel);
      cfm.addEventListener('click', onBackdrop);
      window.addEventListener('keydown', function esc(e){ if(e.key==='Escape'){ cancel(); window.removeEventListener('keydown', esc); } });
    });
  }

  tbody.addEventListener('click', async (e)=>{
    const t = e.target;
    if (t.classList.contains('action-del')){
      const id = parseInt(t.getAttribute('data-id')||'0',10);
      if (!id) return;
      const ok = await showConfirm('Delete this mail log?');
      if (!ok) return;
      await fetch('/APLX/backend/admin/mail_logs.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id })
      });
      load();
    }
    if (t.classList.contains('action-edit')){
      const id = parseInt(t.getAttribute('data-id')||'0',10);
      if (!id) return;
      f.id.value = String(id);
      f.subject.value = t.getAttribute('data-subject')||'';
      const curStatus = (t.getAttribute('data-status')||'').toLowerCase();
      f.status.value = ['sent','failed','queued'].includes(curStatus)? curStatus : 'sent';
      statusEl.textContent = '';
      openModal();
    }
  });

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    statusEl.textContent = 'Saving...';
    const id = parseInt(f.id.value||'0',10);
    if (!id) { statusEl.textContent = 'Invalid id'; return; }
    const subject = f.subject.value.trim();
    const statusVal = f.status.value.trim();
    const r = await fetch('/APLX/backend/admin/mail_logs.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'update', id, subject, status: statusVal })
    });
    if (r.ok) { statusEl.textContent = 'Updated'; closeModal(); load(); } else { statusEl.textContent = 'Save failed'; }
  });

  load();
})();
</script>
</body>
</html>




