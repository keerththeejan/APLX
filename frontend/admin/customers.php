<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Customers</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .toolbar{display:flex;flex-direction:column;gap:8px;align-items:flex-start;margin:48px 0 12px 0}
    .search-row{width:100%;display:flex;justify-content:space-between;gap:10px;align-items:center}
    .actions-row{display:flex;gap:8px}
    .searchbox{display:flex;gap:8px;align-items:center}
    .searchbox input{padding:10px 12px;border-radius:10px;border:1px solid var(--border);min-width:260px;background:#fff;color:#111}

    table.data{width:100%;border-collapse:separate;border-spacing:0 12px}
    table.data th{color:var(--muted);text-align:left;padding:8px 12px;font-weight:600}
    table.data td{background:#0b1220;border:1px solid var(--border);padding:12px;border-left:none;border-right:none}
    table.data tr{border-radius:14px;box-shadow:0 6px 16px rgba(0,0,0,.25)}
    table.data thead th:first-child{padding-left:16px}
    table.data tbody td:first-child{border-top-left-radius:14px;border-bottom-left-radius:14px;padding-left:16px}
    table.data tbody td:last-child{border-top-right-radius:14px;border-bottom-right-radius:14px}

    .cust{display:flex;align-items:center;gap:12px}
    .avatar{width:42px;height:42px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#1f2937;color:#e5e7eb;font-weight:700}
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
      <h2>Customers</h2>
      <div class="search-row">
        <div class="searchbox">
          <input id="q" type="search" placeholder="Search by name or email" />
          <button class="btn" id="btnSearch">Search</button>
        </div>
        <div class="actions-row">
          <button class="btn" id="btnAdd">Add Customer</button>
        </div>
      </div>
    </div>

    <div class="card">
      <table class="data" aria-label="Customers">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Contact</th>
            <th>Location</th>
            <th>Registered</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="custTbody"></tbody>
      </table>
      <div class="pager">
        <button class="btn btn-sm" id="prevPg">Prev</button>
        <span id="pgInfo" class="muted">–</span>
        <button class="btn btn-sm" id="nextPg">Next</button>
      </div>
    </div>
  </main>
</div>

<!-- Create/Edit Modal -->
<div id="custModal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-panel" style="max-width:720px">
    <div class="modal-header">
      <h3 class="modal-title" id="custModalTitle">Customer</h3>
      <button class="modal-close" id="custModalClose" type="button" aria-label="Close">✕</button>
    </div>
    <div class="modal-body">
      <form id="custForm" class="form-grid">
        <input type="hidden" name="csrf" id="csrfField" />
        <input type="hidden" name="id" id="f_id" />
        <div class="form-row two">
          <input type="text" name="name" id="f_name" placeholder="Full Name" required />
          <input type="email" name="email" id="f_email" placeholder="Email" required />
        </div>
        <div class="form-row two">
          <input type="text" name="phone" id="f_phone" placeholder="Phone" />
          <input type="text" name="address" id="f_address" placeholder="Address" />
        </div>
        <div class="form-row two">
          <input type="text" name="district" id="f_district" placeholder="District" />
          <input type="text" name="province" id="f_province" placeholder="Province" />
        </div>
        <div class="form-row two">
          <select name="status" id="f_status">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <input type="password" name="password" id="f_password" placeholder="Password (leave blank to keep)" />
        </div>
        <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end">
          <button type="button" class="btn btn-secondary" id="custCancel">Cancel</button>
          <button type="submit" class="btn">Save</button>
        </div>
      </form>
      <div id="custStatus" class="inline-status" aria-live="polite"></div>
    </div>
  </div>
</div>

<script src="/APLX/js/admin.js"></script>
<script>
(function(){
  const tbody = document.getElementById('custTbody');
  const q = document.getElementById('q');
  const btnSearch = document.getElementById('btnSearch');
  const prevPg = document.getElementById('prevPg');
  const nextPg = document.getElementById('nextPg');
  const pgInfo = document.getElementById('pgInfo');
  const btnAdd = document.getElementById('btnAdd');
  let page = 1, limit = 10, total = 0;

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }
  function getInitials(n){ const p=String(n||'').trim().split(/\s+/).filter(Boolean); if(!p.length) return '??'; return (p[0][0]||'').toUpperCase() + ((p[1]?.[0]||'').toUpperCase()); }
  function formatDateTime(s){ if(!s) return {date:'',time:''}; const d=new Date(s); if(isNaN(d.getTime())) return {date:escapeHtml(String(s)),time:''}; return {date:d.toLocaleDateString(undefined,{day:'2-digit',month:'short',year:'numeric'}), time:d.toLocaleTimeString(undefined,{hour:'2-digit',minute:'2-digit'})}; }

  async function load(){
    const params = new URLSearchParams({ page, limit, search: q.value.trim() });
    const res = await fetch('/APLX/backend/router.php?route=admin/customers&' + params.toString());
    const data = await res.json();
    total = data.total || 0;
    renderRows(data.items||[]);
    const maxPg = Math.max(1, Math.ceil(total/limit));
    pgInfo.textContent = `Page ${page} / ${maxPg} — ${total} customers`;
    prevPg.disabled = page<=1; nextPg.disabled = page>=maxPg;
  }

  function renderRows(items){
    tbody.innerHTML = items.map(u => {
      const name = String(u.name||'').trim();
      const initials = getInitials(name);
      const email = escapeHtml(u.email||'');
      const phone = escapeHtml(u.phone||'');
      const loc = [u.district||'', u.province||''].filter(Boolean).join(', ');
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
        <td class="addr">${escapeHtml(loc)}</td>
        <td class="reg">${created.date}<small>${created.time}</small></td>
        <td><span class="badge ${status==='Active'?'ok':''}">${status}</span></td>
        <td>
          <div class="actions">
            <button class="btn btn-sm" data-id="${u.id}" data-act="edit">Edit</button>
            <button class="btn btn-sm btn-danger" data-id="${u.id}" data-act="del">Delete</button>
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  // Modal logic
  const modal = document.getElementById('custModal');
  const closeBtn = document.getElementById('custModalClose');
  const cancelBtn = document.getElementById('custCancel');
  const form = document.getElementById('custForm');
  const statusEl = document.getElementById('custStatus');
  const titleEl = document.getElementById('custModalTitle');
  const f = {
    id: document.getElementById('f_id'),
    name: document.getElementById('f_name'),
    email: document.getElementById('f_email'),
    phone: document.getElementById('f_phone'),
    address: document.getElementById('f_address'),
    district: document.getElementById('f_district'),
    province: document.getElementById('f_province'),
    status: document.getElementById('f_status'),
    password: document.getElementById('f_password'),
    csrf: document.getElementById('csrfField')
  };

  function openModal(){ modal.classList.add('open'); modal.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
  function closeModal(){ modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
  closeBtn.addEventListener('click', closeModal);
  cancelBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e)=>{ if(e.target===modal) closeModal(); });
  window.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal(); });

  async function getCSRF(){ try{ const r=await fetch('/APLX/backend/router.php?route=admin/customers&action=csrf',{cache:'no-store'}); if(r.ok){ const d=await r.json(); f.csrf.value=d.csrf||''; } }catch(_){} }

  async function openEdit(id){
    statusEl.textContent=''; titleEl.textContent = id? 'Edit Customer' : 'Add Customer';
    form.reset();
    for (const el of form.elements) el.disabled = false;
    if (id){
      const r = await fetch('/APLX/backend/router.php?route=admin/customers&id='+encodeURIComponent(id));
      const d = await r.json();
      const u = d.item||{};
      f.id.value = u.id||'';
      f.name.value = u.name||'';
      f.email.value = u.email||'';
      f.phone.value = u.phone||'';
      f.address.value = u.address||'';
      f.district.value = u.district||'';
      f.province.value = u.province||'';
      f.status.value = (u.status===0||u.status==='inactive')? '0' : '1';
    } else {
      f.id.value='';
    }
    await getCSRF();
    openModal();
  }

  btnAdd.addEventListener('click', ()=> openEdit(''));

  tbody.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button[data-act]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    if (btn.dataset.act === 'edit') {
      openEdit(id);
    } else if (btn.dataset.act === 'del') {
      if (!confirm('Delete this customer?')) return;
      const fd = new FormData(); fd.append('csrf', f.csrf.value); fd.append('_method','DELETE');
      const r = await fetch('/APLX/backend/router.php?route=admin/customers&id='+encodeURIComponent(id), { method:'POST', body: fd });
      if (r.ok){ load(); } else { alert('Delete failed'); }
    }
  });

  form.addEventListener('submit', async (e)=>{
    e.preventDefault(); statusEl.textContent='Saving...';
    const fd = new FormData(form);
    const r = await fetch('/APLX/backend/router.php?route=admin/customers', { method:'POST', body: fd });
    if (r.ok){ statusEl.textContent='Saved'; closeModal(); load(); } else { statusEl.textContent='Save failed'; }
  });

  btnSearch.addEventListener('click', ()=>{ page=1; load(); });
  q.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); page=1; load(); }});
  prevPg.addEventListener('click', ()=>{ if(page>1){ page--; load(); }});
  nextPg.addEventListener('click', ()=>{ page++; load(); });

  load();
})();
</script>
</body>
</html>
