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
    .searchbox{display:flex;gap:6px;align-items:center;justify-content:flex-end;margin:6px 0}
    .searchbox input{padding:6px 10px;border-radius:8px;border:1px solid var(--border);width:200px;background:#fff;color:#111;font-size:.9rem}
    .searchbox .btn{padding:6px 10px;font-size:.9rem;border-radius:8px}
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
            <th>No</th>
            <th>Tracking</th>
            <th>Receiver</th>
            <th>From</th>
            <th>To</th>
            <th>Price</th>
            <th>Status</th>
            <th>Updated</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="shipTbody">
          <tr><td colspan="9" class="muted">Loading...</td></tr>
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
  let csrf = '';

  async function load(){
    if(!tbody) return;
    tbody.innerHTML = '<tr><td colspan="9" class="muted">Loading...</td></tr>';
    const params = new URLSearchParams({ page:String(page), limit:String(limit), search:(input?.value||'').trim() });
    const res = await fetch('/APLX/backend/admin/shipments.php?'+params.toString());
    if(!res.ok){ tbody.innerHTML = '<tr><td colspan="9" class="muted">Failed to load</td></tr>'; return; }
    const data = await res.json();
    total = Number(data.total||0);
    const items = data.items||[];
    if(items.length===0){ tbody.innerHTML = '<tr><td colspan="9" class="muted">No results</td></tr>'; }
    else {
      tbody.innerHTML = items.map((s, idx) => `
        <tr>
          <td>${(page-1)*limit + idx + 1}</td>
          <td>${escapeHtml(s.tracking_number||'')}</td>
          <td>${escapeHtml(s.receiver_name||'')}</td>
          <td>${escapeHtml(s.origin||'')}</td>
          <td>${escapeHtml(s.destination||'')}</td>
          <td>${escapeHtml(s.price==null?'':String(s.price))}</td>
          <td>${escapeHtml(s.status||'')}</td>
          <td>${escapeHtml(s.updated_at||'')}</td>
          <td class="actions">
            <button class="btn-icon btn-blue action-edit" title="Edit" data-id="${s.id}" data-status="${escapeHtml(s.status||'')}">✏️</button>
            <button class="btn-icon btn-red action-del" title="Delete" data-id="${s.id}">🗑️</button>
          </td>
        </tr>
      `).join('');
    }
    const maxPg = Math.max(1, Math.ceil(total/limit));
    if(pgInfo) pgInfo.textContent = `Page ${page} / ${maxPg} — ${total} rows`;
    if(prevPg) prevPg.disabled = page<=1;
    if(nextPg) nextPg.disabled = page>=maxPg;
  }

  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }

  async function getCSRF(){
    try{ const r = await fetch('/APLX/backend/admin/customers_api.php?action=csrf',{cache:'no-store'}); if(r.ok){ const d = await r.json(); csrf = d.csrf||''; } }catch(e){}
  }

  // Edit Modal (centered dark theme)
  const modal = document.createElement('div');
  modal.id = 'shipModal';
  modal.className = 'modal-backdrop';
  modal.setAttribute('aria-hidden','true');
  modal.setAttribute('role','dialog');
  modal.setAttribute('aria-modal','true');
  modal.innerHTML = `
    <div class="modal-panel" style="max-width:520px">
      <div class="modal-header">
        <h3 class="modal-title">Update Shipment Status</h3>
        <button class="modal-close" id="shipModalClose" type="button" aria-label="Close">✕</button>
      </div>
      <div class="modal-body">
        <form id="shipForm" class="form-grid">
          <input type="hidden" id="ship_id" />
          <div class="form-row">
            <select id="ship_status" required>
              <option value="Booked">Booked</option>
              <option value="pending">pending</option>
              <option value="in_transit">in_transit</option>
              <option value="delivered">delivered</option>
              <option value="cancelled">cancelled</option>
            </select>
          </div>
          <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end">
            <button type="submit" class="btn">Save</button>
            <button type="button" class="btn btn-danger" id="shipCancel">Cancel</button>
          </div>
        </form>
        <div id="shipStatusMsg" class="inline-status" aria-live="polite"></div>
      </div>
    </div>`;
  document.body.appendChild(modal);
  const shipForm = modal.querySelector('#shipForm');
  const shipIdEl = modal.querySelector('#ship_id');
  const shipStatusEl = modal.querySelector('#ship_status');
  const shipCloseBtn = modal.querySelector('#shipModalClose');
  const shipCancelBtn = modal.querySelector('#shipCancel');
  const shipMsgEl = modal.querySelector('#shipStatusMsg');
  function openModal(){ modal.classList.add('open'); modal.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
  function closeModal(){ modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
  shipCloseBtn.addEventListener('click', closeModal);
  shipCancelBtn.addEventListener('click', closeModal);
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

  tbody?.addEventListener('click', async (e)=>{
    const btn = e.target.closest('.action-edit, .action-del');
    if(!btn) return;
    const id = btn.getAttribute('data-id');
    if(!id) return;
    if(!csrf) await getCSRF();
    if(btn.classList.contains('action-del')){
      const ok = await showConfirm('Delete this shipment?');
      if(!ok) return;
      const fd = new FormData(); fd.append('csrf', csrf); fd.append('_method','DELETE');
      const r = await fetch('/APLX/backend/admin/shipments.php?id='+encodeURIComponent(id), { method:'POST', body: fd });
      if(r.ok){ showCenterPopup('Shipment deleted','success'); load(); } else { showCenterPopup('Delete failed','error'); }
    } else if(btn.classList.contains('action-edit')){
      shipMsgEl.textContent='';
      shipIdEl.value = id;
      const cur = (btn.getAttribute('data-status')||'').trim();
      // Preselect if matches one of options (case-insensitive)
      const opts = Array.from(shipStatusEl.options).map(o=>o.value);
      const match = opts.find(o=>o.toLowerCase()===cur.toLowerCase());
      shipStatusEl.value = match || 'Booked';
      openModal();
    }
  });

  shipForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    shipMsgEl.textContent='Saving...';
    const id = shipIdEl.value;
    const statusVal = shipStatusEl.value;
    const fd = new FormData(); fd.append('csrf', csrf); fd.append('_method','PATCH'); fd.append('status', statusVal);
    const r = await fetch('/APLX/backend/admin/shipments.php?id='+encodeURIComponent(id), { method:'POST', body: fd });
    if(r.ok){ shipMsgEl.textContent='Updated'; closeModal(); showCenterPopup('Status updated','success'); load(); } else { shipMsgEl.textContent='Update failed'; showCenterPopup('Update failed','error'); }
  });
  btnSearch?.addEventListener('click', ()=>{ page=1; load(); });
  input?.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); page=1; load(); }});
  prevPg?.addEventListener('click', ()=>{ if(page>1){ page--; load(); }});
  nextPg?.addEventListener('click', ()=>{ page++; load(); });

  getCSRF().then(load);

  // Centered popup styles & function
  const centerStyles = document.createElement('style');
  centerStyles.textContent = `
    .center-popup-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;z-index:10000;opacity:0;transition:opacity .2s}
    .center-popup-overlay.show{opacity:1}
    .center-popup{background:#0f172a;color:#e2e8f0;border:1px solid rgba(255,255,255,.08);padding:18px 22px;border-radius:12px;min-width:260px;max-width:90vw;box-shadow:0 20px 40px rgba(0,0,0,.45);text-align:center}
    .center-popup.success{border-left:4px solid #10b981}
    .center-popup.error{border-left:4px solid #ef4444}
  `;
  document.head.appendChild(centerStyles);
  function showCenterPopup(message, type){
    const overlay = document.createElement('div');
    overlay.className='center-popup-overlay';
    const box = document.createElement('div');
    box.className='center-popup '+(type==='error'?'error':'success');
    box.textContent = message;
    overlay.appendChild(box);
    document.body.appendChild(overlay);
    requestAnimationFrame(()=> overlay.classList.add('show'));
    setTimeout(()=>{ overlay.classList.remove('show'); setTimeout(()=> overlay.remove(), 200); }, 1800);
  }
})();
</script>
</body>
</html>
