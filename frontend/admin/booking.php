<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Booking</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .toolbar{display:flex;gap:10px;align-items:center;justify-content:space-between;margin:48px 0 12px 0}
    .searchbox{display:flex;gap:8px;align-items:center}
    .searchbox input{padding:10px 12px;border-radius:10px;border:1px solid var(--border);min-width:260px;background:#fff;color:#111}
    .pager{display:flex;gap:8px;align-items:center;justify-content:flex-end;margin-top:10px}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
  <div id="topbar"></div>
  <div class="toolbar" id="topActions" style="flex-direction:column;align-items:flex-start;gap:8px">
    <h2 style="margin:0">Bookings</h2>
    <div><button class="btn" id="btnAddBooking">Add Booking</button></div>
  </div>
  <section class="card" id="createCard" style="display:none; max-width:520px; margin:0 auto; position:relative">
    <h2>Create Booking</h2>
    <button id="btnCloseCreateTop" class="modal-close" type="button" aria-label="Close" style="position:absolute; right:12px; top:12px">✕</button>
    <form id="bookForm" method="post" action="/APLX/backend/book_submit.php">
      <div class="grid">
        <input type="text" name="sender_name" placeholder="Sender Name" required>
        <input type="text" name="receiver_name" placeholder="Receiver Name" required>
        <input type="text" name="origin" placeholder="Origin City" required>
        <input type="text" name="destination" placeholder="Destination City" required>
        <input type="number" step="0.01" name="weight" placeholder="Weight (kg)" required>
        <input type="number" step="0.01" name="price" placeholder="Price (optional)">
      </div>
      <div style="display:flex;gap:8px;align-items:center;margin-top:10px">
        <button class="btn" type="submit">Create Booking</button>
        <a class="btn btn-outline" href="/APLX/frontend/customer/book.php">Use Customer Form</a>
        <span id="bookStatus" class="muted" aria-live="polite"></span>
      </div>
    </form>
  </section>

  <div class="toolbar" id="recentToolbar">
    <h2 style="margin:0">Recent Bookings</h2>
    <div class="searchbox">
      <input id="q" type="search" placeholder="Search by tracking, receiver, city" />
      <button class="btn" id="btnSearch">Search</button>
    </div>
  </div>

  <section class="card" id="recentCard">
    <div class="table-responsive">
      <table class="data">
        <thead>
          <tr>
            <th>Tracking</th>
            <th>Sender</th>
            <th>Receiver</th>
            <th>From</th>
            <th>To</th>
            <th>Weight (kg)</th>
            <th>Price</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="bookTbody">
          <tr><td colspan="6" class="muted">Loading...</td></tr>
        </tbody>
      </table>
    </div>
    <div class="pager">
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
  // List controls
  const q = document.getElementById('q');
  const btnSearch = document.getElementById('btnSearch');
  const tbody = document.getElementById('bookTbody');
  const prevPg = document.getElementById('prevPg');
  const nextPg = document.getElementById('nextPg');
  const pgInfo = document.getElementById('pgInfo');
  let page = 1, limit = 10, total = 0;

  async function load(){
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="6" class="muted">Loading...</td></tr>';
    const params = new URLSearchParams({ page:String(page), limit:String(limit), search:(q?.value||'').trim() });
    const res = await fetch('/APLX/backend/admin/shipments.php?api=1&'+params.toString(), { cache:'no-store' });
    if (!res.ok){ tbody.innerHTML = '<tr><td colspan="6" class="muted">Failed to load</td></tr>'; return; }
    const data = await res.json();
    total = Number(data.total||0);
    const items = Array.isArray(data.items)?data.items:[];
    if (items.length===0){ tbody.innerHTML = '<tr><td colspan="10" class="muted">No results</td></tr>'; }
    else{
      tbody.innerHTML = items.map(s => `
        <tr>
          <td>${escapeHtml(s.tracking_number||'')}</td>
          <td>${escapeHtml(s.sender_name||'')}</td>
          <td>${escapeHtml(s.receiver_name||'')}</td>
          <td>${escapeHtml(s.origin||'')}</td>
          <td>${escapeHtml(s.destination||'')}</td>
          <td>${escapeHtml(String(s.weight??''))}</td>
          <td>${escapeHtml(s.price==null?'':String(s.price))}</td>
          <td>${escapeHtml(s.status||'')}</td>
          <td>${escapeHtml(s.created_at||'')}</td>
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
  q?.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); page=1; load(); } });
  prevPg?.addEventListener('click', ()=>{ if(page>1){ page--; load(); } });
  nextPg?.addEventListener('click', ()=>{ page++; load(); });

  // Show/Hide create booking form
  const createCard = document.getElementById('createCard');
  const btnAddBooking = document.getElementById('btnAddBooking');
  const btnCloseCreateTop = document.getElementById('btnCloseCreateTop');
  const recentToolbar = document.getElementById('recentToolbar');
  const recentCard = document.getElementById('recentCard');
  btnAddBooking?.addEventListener('click', ()=>{
    if(createCard){
      createCard.style.display='block';
      // Hide other sections so only the form is visible
      if(recentToolbar) recentToolbar.style.display='none';
      if(recentCard) recentCard.style.display='none';
      window.scrollTo({ top: createCard.offsetTop - 70, behavior:'smooth' });
    }
  });
  btnCloseCreateTop?.addEventListener('click', ()=>{ window.location.href = '/APLX/frontend/admin/booking.php'; });

  // AJAX submit for booking form -> saves to DB using existing endpoint, then reload list
  const form = document.getElementById('bookForm');
  const statusEl = document.getElementById('bookStatus');
  form?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    statusEl && (statusEl.textContent = 'Saving...');
    try{
      const fd = new FormData(form);
      const res = await fetch(form.action, { method: 'POST', body: fd });
      if (!res.ok) throw new Error('Request failed');
      statusEl && (statusEl.textContent = 'Created');
      form.reset();
      // Refresh list
      page = 1; await load();
    }catch(err){
      statusEl && (statusEl.textContent = 'Save failed');
    }
  });

  // initial load
  load();
})();
</script>
</body>
</html>




