<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Users</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .content{padding:16px;margin-left:260px}
    .toolbar{display:flex;flex-direction:column;gap:8px;align-items:flex-start;margin:48px 0 12px 0}
    .search-row{width:100%;display:flex;justify-content:flex-end}
    .card{margin-top:10px}
    .searchbox{display:flex;gap:8px;align-items:center}
    .searchbox input{padding:10px 12px;border-radius:10px;border:1px solid var(--border);min-width:260px;background:#fff;color:#111}

    /* Table base */
    table.data{width:100%;border-collapse:separate;border-spacing:0 12px}
    table.data th{color:var(--muted);text-align:left;padding:8px 12px;font-weight:600}
    table.data td{background:#0b1220;border:1px solid var(--border);padding:12px;border-left:none;border-right:none}
    table.data tr{border-radius:14px;box-shadow:0 6px 16px rgba(0,0,0,.25)}
    table.data thead th:first-child{padding-left:16px}
    table.data tbody td:first-child{border-top-left-radius:14px;border-bottom-left-radius:14px;padding-left:16px}
    table.data tbody td:last-child{border-top-right-radius:14px;border-bottom-right-radius:14px}

    /* Customer cell */
    .cust{display:flex;align-items:center;gap:12px}
    .avatar{width:42px;height:42px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#1f2937;color:#e5e7eb;font-weight:700}
    .c-meta{display:flex;flex-direction:column}
    .c-name{font-weight:600}
    .c-sub{color:var(--muted);font-size:12px}

    /* Contact cell */
    .contact{display:flex;flex-direction:column;gap:2px}
    .contact .email{color:#cbd5e1}
    .contact .phone{color:var(--muted);font-size:12px}

    /* Address pill */
    .addr{max-width:380px}
    .addr-chip{display:block;background:#ffffff;color:#111;padding:10px 12px;border-radius:14px;box-shadow:inset 0 0 0 1px rgba(0,0,0,.06)}

    /* Registered */
    .reg{white-space:nowrap;color:#cbd5e1}
    .reg small{display:block;color:var(--muted)}

    /* Status */
    .badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:600}
    .badge.ok{background:#052e1a;color:#22c55e;box-shadow:inset 0 0 0 1px #14532d}

    /* Actions */
    td.actions-cell{min-width:180px}
    .actions{display:flex;gap:10px;justify-content:flex-end}
    .btn-icon{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border:none;border-radius:10px;cursor:pointer;color:#fff;box-shadow:0 6px 14px rgba(0,0,0,.28); transition: transform .12s ease, filter .12s ease}
    .btn-icon:hover{ transform: translateY(-1px); filter: brightness(1.03); }
    .btn-icon:active{ transform: translateY(0); }
    .btn-icon:focus{outline:2px solid #93c5fd; outline-offset:2px}
    .btn-icon svg{width:18px;height:18px;stroke:#fff;stroke-width:2;fill:none}
    .btn-icon.view{background:#16a34a; border:1px solid #15803d}
    .btn-icon.view:hover{background:#15803d}
    .btn-icon.edit{background:#3b82f6; border:1px solid #2563eb}
    .btn-icon.edit:hover{background:#2563eb}
    .btn-icon.del{background:#ef4444; border:1px solid #dc2626}
    .btn-icon.del:hover{background:#dc2626}
    .pager{display:flex;gap:8px;align-items:center;justify-content:flex-end;margin-top:10px}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>

    <div class="toolbar">
      <h2>Users</h2>
      <div class="search-row" style="display:flex;justify-content:flex-end">
        <div class="searchbox">
          <input id="q" type="search" placeholder="Search by name or email" />
          <button class="btn" id="btnSearch">Search</button>
        </div>
      </div>
    </div>

    <div class="card">
      <table class="data">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Registered</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="usersTbody"></tbody>
      </table>
      <div class="pager">
        <button class="btn btn-sm" id="prevPg">Prev</button>
        <span id="pgInfo" class="muted">–</span>
        <button class="btn btn-sm" id="nextPg">Next</button>
      </div>
    </div>
  </main>
</div>

<!-- View/Edit Modal -->
<div id="userModal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-panel" style="max-width:640px">
    <div class="modal-header">
      <h3 class="modal-title" id="userModalTitle">User</h3>
      <button class="modal-close" id="userModalClose" type="button" aria-label="Close">✕</button>
    </div>
    <div class="modal-body">
      <form id="userForm" class="form-grid">
        <input type="hidden" name="id" id="f_id" />
        <div class="form-row two">
          <input type="text" name="name" id="f_name" placeholder="Full Name" required />
          <input type="email" name="email" id="f_email" placeholder="Email" required />
        </div>
        <div class="form-row two">
          <select name="role" id="f_role" required>
            <option value="customer">Customer</option>
            <option value="admin">Admin</option>
          </select>
          <input type="password" name="password" id="f_password" placeholder="New Password (optional)" />
        </div>
        <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end">
          <button type="button" class="btn btn-secondary" id="userCancel">Cancel</button>
          <button type="submit" class="btn">Save</button>
        </div>
      </form>
      <div id="userStatus" class="inline-status" aria-live="polite"></div>
    </div>
  </div>
</div>

<script src="/APLX/js/admin.js"></script>
<script>
(function(){
  const tbody = document.getElementById('usersTbody');
  const q = document.getElementById('q');
  const btnSearch = document.getElementById('btnSearch');
  const prevPg = document.getElementById('prevPg');
  const nextPg = document.getElementById('nextPg');
  const pgInfo = document.getElementById('pgInfo');
  let page = 1, limit = 10, total = 0;

  async function load(){
    const params = new URLSearchParams({ page, limit, search: q.value.trim() });
    const res = await fetch('/APLX/backend/router.php?route=admin/users&' + params.toString());
    const data = await res.json();
    total = data.total || 0;
    renderRows(data.items||[]);
    const maxPg = Math.max(1, Math.ceil(total/limit));
    pgInfo.textContent = `Page ${page} / ${maxPg} — ${total} users`;
    prevPg.disabled = page<=1; nextPg.disabled = page>=maxPg;
  }

  function renderRows(items){
    tbody.innerHTML = items.map(u => {
      const name = String(u.name||'').trim();
      const initials = getInitials(name);
      const email = escapeHtml(u.email||'');
      const phone = escapeHtml(u.phone||'');
      const address = escapeHtml(u.address||'');
      const created = formatDateTime(u.created_at);
      const status = (u.status===0 || u.status==='inactive') ? 'Inactive' : 'Active';
      return `
      <tr>
        <td>
          <div class="cust">
            <div class="avatar" aria-hidden="true">${initials}</div>
            <div class="c-meta">
              <div class="c-name">${escapeHtml(name||'Unknown')}</div>
              <div class="c-sub">#${u.id||''}</div>
            </div>
          </div>
        </td>
        <td>
          <div class="contact">
            <span class="email">${email}</span>
            <span class="phone">${phone}</span>
          </div>
        </td>
        <td class="addr">
          ${address ? `<span class="addr-chip">${address}</span>` : ''}
        </td>
        <td class="reg">${created.date}<small>${created.time}</small></td>
        <td><span class="badge ${status==='Active'?'ok':''}">${status}</span></td>
        <td>
          <div class="actions">
            <button class="btn-icon view" data-id="${u.id}" data-act="view" title="View" aria-label="View">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
            <button class="btn-icon edit" data-id="${u.id}" data-act="edit" title="Edit" aria-label="Edit">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
            </button>
            <button class="btn-icon del" data-id="${u.id}" data-act="del" title="Delete" aria-label="Delete">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
            </button>
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }
  function getInitials(n){
    const parts = String(n||'').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '??';
    const a = parts[0][0]||''; const b = (parts[1]?.[0])||''; return (a + b).toUpperCase();
  }
  function formatDateTime(s){
    if (!s) return {date:'', time:''};
    const d = new Date(s);
    if (isNaN(d.getTime())) return {date: escapeHtml(String(s)), time:''};
    const dd = d.toLocaleDateString(undefined, { day:'2-digit', month:'short', year:'numeric' });
    const tt = d.toLocaleTimeString(undefined, { hour:'2-digit', minute:'2-digit' });
    return { date: dd, time: tt };
  }

  btnSearch.addEventListener('click', ()=>{ page=1; load(); });
  q.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); page=1; load(); }});
  prevPg.addEventListener('click', ()=>{ if(page>1){ page--; load(); }});
  nextPg.addEventListener('click', ()=>{ page++; load(); });

  tbody.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button[data-act]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    const act = btn.getAttribute('data-act');
    if (act==='view' || act==='edit') {
      openUserModal(id, act==='edit');
    } else if (act==='del') {
      if (confirm('Delete this user?')){
        const res = await fetch('/APLX/backend/router.php?route=admin/users&id='+encodeURIComponent(id), { method: 'DELETE' });
        const ok = res.ok;
        if (ok) load(); else alert('Delete failed');
      }
    }
  });

  // Modal
  const modal = document.getElementById('userModal');
  const closeBtn = document.getElementById('userModalClose');
  const cancelBtn = document.getElementById('userCancel');
  const form = document.getElementById('userForm');
  const statusEl = document.getElementById('userStatus');
  const titleEl = document.getElementById('userModalTitle');
  async function openUserModal(id, editable){
    statusEl.textContent = '';
    titleEl.textContent = editable? 'Edit User' : 'View User';
    form.reset();
    for (const el of form.elements) el.disabled = !editable && el.name!=='id';
    const res = await fetch('/APLX/backend/router.php?route=admin/users&id='+encodeURIComponent(id));
    const data = await res.json();
    const u = data.item || {};
    document.getElementById('f_id').value = u.id||'';
    document.getElementById('f_name').value = u.name||'';
    document.getElementById('f_email').value = u.email||'';
    document.getElementById('f_role').value = u.role||'customer';
    openModal();
  }
  function openModal(){ modal.classList.add('open'); modal.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
  function closeModal(){ modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
  closeBtn.addEventListener('click', closeModal);
  cancelBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e)=>{ if(e.target===modal) closeModal(); });
  window.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal(); });

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    statusEl.textContent = 'Saving...';
    const fd = new FormData(form);
    const res = await fetch('/APLX/backend/router.php?route=admin/users', { method:'POST', body: fd });
    if (res.ok) { statusEl.textContent = 'Saved'; closeModal(); load(); } else { statusEl.textContent = 'Save failed'; }
  });

  // First load
  load();
})();
</script>
</body>
</html>
