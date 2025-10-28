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
    .btn-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 4px;
      text-decoration: none;
      border: none;
      cursor: pointer;
      font-size: 16px;
    }
    .btn-blue {
      background-color: #1d4ed8;
      color: white;
    }
    .btn-red {
      background-color: #dc2626;
      color: white;
    }
    .btn-icon:hover {
      opacity: 0.9;
    }
    .actions {
      display: flex;
      gap: 4px;
    }
    /* Action buttons */
    .actions {
      display: flex;
      gap: 8px;
      justify-content: center;
    }
    .btn-icon {
      width: 32px;
      height: 32px;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border: none;
      transition: opacity 0.2s;
    }
    .btn-icon:hover {
      opacity: 0.8;
    }
    .btn-blue {
      background-color: #3b82f6;
      color: white;
    }
    .btn-red {
      background-color: #ef4444;
      color: white;
    }
    /* Toast notification */
    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      padding: 12px 20px;
      border-radius: 4px;
      color: white;
      z-index: 1000;
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.3s, transform 0.3s;
    }
    .toast.show {
      opacity: 1;
      transform: translateY(0);
    }
    .toast.success {
      background-color: #10b981;
    }
    .toast.error {
      background-color: #ef4444;
    }
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
            <th>No</th>
            <th>Tracking</th>
            <th>Sender</th>
            <th>Receiver</th>
            <th>From</th>
            <th>To</th>
            <th>Weight (kg)</th>
            <th>Price</th>
            <th>Status</th>
            <th>Created</th>
            <th>Updated</th>
            <th style="width: 100px;">Actions</th>
          </tr>
        </thead>
        <tbody id="bookTbody">
          <tr><td colspan="12" class="muted">Loading...</td></tr>
        </tbody>
      </table>
    </div>
    <div class="pager">
      <button class="btn btn-sm" id="prevPg">Prev</button>
      <span id="pgInfo" class="muted">–</span>
      <button class="btn btn-sm" id="nextPg">Next</button>
    </div>
  </section>
  
  <!-- Edit Booking Modal -->
  <div id="editBookingModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
    <div class="modal-content" style="background: #1e1e2e; padding: 20px; border-radius: 12px; width: 100%; max-width: 800px; color: #e2e8f0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); border: 1px solid rgba(255, 255, 255, 0.1); max-height: 90vh; display: flex; flex-direction: column;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
        <h3 style="margin: 0; color: #f8fafc; font-weight: 600; font-size: 1.1rem;">Update Booking Status</h3>
        <button id="closeEditModal" style="background: none; border: none; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #94a3b8; transition: all 0.2s; font-size: 20px; padding: 0;">&times;</button>
      </div>
      <div style="overflow-y: auto; max-height: calc(90vh - 150px); padding-right: 8px; margin-right: -8px;">
        <form id="editBookingForm">
          <div id="bookingDetails" style="display: flex; flex-direction: column; gap: 12px;"></div>
          <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
              <button type="button" id="cancelEdit" style="padding: 8px 16px; background: rgba(255, 255, 255, 0.05); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500; font-size: 0.9rem;">
                Cancel
              </button>
              <button type="submit" id="saveBooking" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 6px;">
                <span id="saveButtonText">Save Changes</span>
                <span id="saveButtonLoader" style="display: none;" class="loading-spinner"></span>
              </button>
            </div>
          </div>
        </form>
      </div>
      </form>
    </div>
  </div>

  <style>
    /* Booking Details Styles */
    .booking-details {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    
    .details-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
      margin-bottom: 15px;
    }
    
    @media (max-width: 1200px) {
      .details-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    @media (max-width: 768px) {
      .details-grid {
        grid-template-columns: 1fr;
      }
    }
    
    .detail-item {
      display: flex;
      flex-direction: column;
      padding: 12px;
      background: rgba(255, 255, 255, 0.02);
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.06);
      transition: all 0.2s ease;
      min-height: 50px;
    }
    
    .detail-item:hover {
      background: rgba(255, 255, 255, 0.05);
      transform: translateY(-2px);
    }
    
    .detail-label {
      font-size: 0.75rem;
      color: #94a3b8;
      margin-bottom: 2px;
      margin-bottom: 4px;
    }
    
    .detail-value {
      font-size: 0.95rem;
      color: #e2e8f0;
      word-break: break-word;
    }
    
    .status-update-section {
      background: rgba(255, 255, 255, 0.03);
      padding: 16px;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.06);
      margin: 15px 0;
    }
    
    /* Form Styles */
    .form-group { 
      margin-bottom: 16px;
    }
    .form-group:hover {
      border-color: rgba(255, 255, 255, 0.1);
      background: rgba(255, 255, 255, 0.05);
    }
    .form-group label { 
      display: block; 
      margin-bottom: 8px; 
      font-weight: 500;
      color: #e2e8f0;
      font-size: 0.875rem;
      letter-spacing: 0.02em;
    }
    .form-group input, 
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px 14px;
      background: rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      color: #f8fafc;
      font-size: 0.9375rem;
      transition: all 0.2s;
      font-family: inherit;
    }
    .form-group input:focus, 
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
      background: rgba(0, 0, 0, 0.3);
    }
    .form-group select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
      background-position: right 0.75rem center;
      background-repeat: no-repeat;
      background-size: 1.25em 1.25em;
      padding-right: 2.5rem;
      cursor: pointer;
    }
    
    /* Status Badges */
    .status-badge {
      display: inline-flex;
      align-items: center;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.8125rem;
      font-weight: 500;
      text-transform: capitalize;
      letter-spacing: 0.02em;
      transition: all 0.2s;
      border: 1px solid transparent;
    }
    .status-pending { 
      background-color: rgba(245, 158, 11, 0.15); 
      color: #f59e0b; 
      border-color: rgba(245, 158, 11, 0.2); 
    }
    .status-in_transit { 
      background-color: rgba(59, 130, 246, 0.15); 
      color: #60a5fa; 
      border-color: rgba(59, 130, 246, 0.2); 
    }
    .status-delivered { 
      background-color: rgba(16, 185, 129, 0.15); 
      color: #34d399; 
      border-color: rgba(16, 185, 129, 0.2); 
    }
    .status-cancelled { 
      background-color: rgba(239, 68, 68, 0.15); 
      color: #f87171; 
      border-color: rgba(239, 68, 68, 0.2); 
    }
    
    /* Modal Transitions */
    .modal {
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .modal.show {
      opacity: 1;
      visibility: visible;
    }
    .modal-content {
      transform: translateY(10px) scale(0.98);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      opacity: 0;
    }
    .modal.show .modal-content {
      transform: translateY(0) scale(1);
      opacity: 1;
    }
    
    /* Button Hover Effects */
    #closeEditModal {
      transition: all 0.2s;
    }
    #closeEditModal:hover {
      background: rgba(255, 255, 255, 0.1);
      color: #f8fafc;
      transform: rotate(90deg);
    }
    #cancelEdit {
      transition: all 0.2s;
    }
    #cancelEdit:hover {
      background: rgba(255, 255, 255, 0.1) !important;
      border-color: rgba(255, 255, 255, 0.15);
    }
    #saveBooking {
      position: relative;
      overflow: hidden;
      transition: all 0.3s;
    }
    #saveBooking::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
      transition: 0.5s;
    }
    #saveBooking:hover {
      background: #2563eb !important;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }
    #saveBooking:hover::before {
      left: 100%;
    }
    
    /* Loading Spinner */
    .loading-spinner {
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: white;
      animation: spin 0.6s linear infinite;
      opacity: 0;
      transition: opacity 0.2s;
    }
    
    #saveButtonLoader.loading {
      opacity: 1;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* Loading State */
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    .loading {
      position: relative;
      pointer-events: none;
      opacity: 0.8;
    }
    .loading::after {
      content: '';
      display: inline-block;
      width: 16px;
      height: 16px;
      margin-left: 8px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: white;
      animation: spin 0.6s linear infinite;
      vertical-align: middle;
    }
  </style>
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
  let csrf = '';

  async function load(){
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="12" class="muted">Loading...</td></tr>';
    const params = new URLSearchParams({ page:String(page), limit:String(limit), search:(q?.value||'').trim() });
    const res = await fetch('/APLX/backend/admin/shipments.php?api=1&'+params.toString(), { cache:'no-store' });
    if (!res.ok){ tbody.innerHTML = '<tr><td colspan="12" class="muted">Failed to load</td></tr>'; return; }
    const data = await res.json();
    total = Number(data.total||0);
    const items = Array.isArray(data.items)?data.items:[];
    if (items.length===0){ tbody.innerHTML = '<tr><td colspan="12" class="muted">No results</td></tr>'; }
    else{
      tbody.innerHTML = items.map((s, idx) => `
        <tr data-id="${s.id}">
          <td>${(page-1)*limit + idx + 1}</td>
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
          <td class="actions">
            <button class="btn-icon btn-blue edit-booking" title="Edit">✏️</button>
            <button class="btn-icon btn-red delete-btn" title="Delete" data-id="${s.id}">🗑️</button>
             
            </form>
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

  // Fetch CSRF token (reuse existing admin endpoint)
  async function getCSRF(){
    try{
      const r = await fetch('/APLX/backend/admin/customers_api.php?action=csrf',{cache:'no-store'});
      if(r.ok){ const d = await r.json(); csrf = d.csrf||''; }
    }catch(_){ /* ignore */ }
  }

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

  // Edit Booking Modal
  const editBookingModal = document.getElementById('editBookingModal');
  const closeEditModal = document.getElementById('closeEditModal');
  const cancelEdit = document.getElementById('cancelEdit');
  const saveBookingBtn = document.getElementById('saveBooking');
  let currentBookingId = null;
  let currentBookingRow = null;

  // Function to open edit modal
  function openEditModal(booking, row) {
    currentBookingId = booking.id;
    currentBookingRow = row;
    
    const statusOptions = {
      'pending': '🟡 Pending',
      'in_transit': '🚚 In Transit',
      'delivered': '✅ Delivered',
      'cancelled': '❌ Cancelled'
    };
    
    // Create status select options
    let statusSelect = `
      <select id="bookingStatus" class="form-control" style="padding-right: 2.5rem;">
        <option value="">Select Status</option>
    `;
    
    for (const [value, label] of Object.entries(statusOptions)) {
      const isSelected = booking.status === value ? 'selected' : '';
      statusSelect += `<option value="${value}" ${isSelected}>${label}</option>`;
    }
    statusSelect += '</select>';
    
    // Get all booking details from the row
    const bookingDetails = {
      'Tracking Number': booking.tracking_number || 'N/A',
      'Sender Name': row.cells[1]?.textContent.trim() || 'N/A',
      'Receiver Name': row.cells[2]?.textContent.trim() || 'N/A',
      'Origin': row.cells[3]?.textContent.trim() || 'N/A',
      'Destination': row.cells[4]?.textContent.trim() || 'N/A',
      'Weight': row.cells[5]?.textContent.trim() || 'N/A',
      'Price': row.cells[6]?.textContent.trim() || 'N/A'
    };
    
    // Create details HTML
    let details = `
      <div class="booking-details">
        <div class="details-grid">
          ${Object.entries(bookingDetails).map(([label, value]) => `
            <div class="detail-item">
              <span class="detail-label">${label}:</span>
              <span class="detail-value">${escapeHtml(value)}</span>
            </div>
          `).join('')}
        </div>
        
        <div class="status-update-section">
          <div class="form-group">
            <label>Current Status</label>
            <div class="status-badge status-${escapeHtml(booking.status || 'pending')}" style="display: inline-flex; align-items: center; gap: 6px; margin: 8px 0;">
              ${statusOptions[booking.status] || 'N/A'}
            </div>
          </div>
          
          <div class="form-group">
            <label for="bookingStatus">Update Status</label>
            ${statusSelect}
          </div>
        </div>
      </div>
    `;
    
    document.getElementById('bookingDetails').innerHTML = details;
    
    // Show modal with animation
    editBookingModal.style.display = 'flex';
    setTimeout(() => {
      editBookingModal.classList.add('show');
    }, 10);
    
    // Focus the status select
    setTimeout(() => {
      const statusSelect = document.getElementById('bookingStatus');
      if (statusSelect) statusSelect.focus();
    }, 100);
  }

  // Close modal
  function closeModal() {
    editBookingModal.classList.remove('show');
    setTimeout(() => {
      editBookingModal.style.display = 'none';
      currentBookingId = null;
      currentBookingRow = null;
      document.getElementById('bookingDetails').innerHTML = '';
    }, 200);
  }

  // Event listeners
  closeEditModal.addEventListener('click', closeModal);
  cancelEdit.addEventListener('click', closeModal);

  // Handle form submission (save status)
  document.getElementById('editBookingForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!currentBookingId) return;
    
    const statusSelect = document.getElementById('bookingStatus');
    if (!statusSelect || !statusSelect.value) {
      showToast('Please select a status', 'error');
      statusSelect.focus();
      return;
    }
    
    const status = statusSelect.value;
    const statusText = statusSelect.options[statusSelect.selectedIndex].text;
    const saveButton = document.getElementById('saveBooking');
    const saveButtonText = document.getElementById('saveButtonText');
    const saveButtonLoader = document.getElementById('saveButtonLoader');
    
    try {
      // Show loading state
      saveButton.disabled = true;
      saveButtonText.style.display = 'none';
      saveButtonLoader.style.display = 'inline';
      
      if (!csrf) { await getCSRF(); }
      const fd = new FormData();
      fd.append('csrf', csrf);
      fd.append('_method', 'PATCH');
      fd.append('status', status);
      const response = await fetch('/APLX/backend/admin/shipments.php?id='+encodeURIComponent(currentBookingId), { method:'POST', body: fd });
      if (response.ok) {
        // Also persist to bookings table
        const r2 = await fetch('/APLX/backend/admin/booking_update.php', {
          method: 'POST',
          headers: {},
          body: (()=>{ const f=new FormData(); f.append('csrf', csrf); f.append('id', currentBookingId); f.append('status', status); return f; })()
        });
        // Update the status in the table
        if (currentBookingRow) {
          const statusCell = currentBookingRow.cells[8]; // Status column index in this table
          if (statusCell) {
            // Update status text and class
            statusCell.textContent = status;
          }
        }
        
        showToast('✅ Booking status updated successfully', 'success');
        closeModal();
        // Also refresh list to reflect updated time
        await load();
      } else {
        showToast('❌ Failed to update booking', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showToast('❌ An error occurred while updating the booking', 'error');
    } finally {
      // Reset button state
      if (saveButton) {
        saveButton.disabled = false;
        saveButtonText.style.display = 'inline';
        saveButtonLoader.style.display = 'none';
      }
    }
  });

  // Close modal when clicking outside
  editBookingModal.addEventListener('click', (e) => {
    if (e.target === editBookingModal) {
      closeModal();
    }
  });

  // Update the load function to add click handlers for edit buttons
  const originalLoad = load;
  load = async function() {
    await originalLoad.apply(this, arguments);
    
    // Add click handlers for all edit buttons
    document.querySelectorAll('.edit-booking').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const row = btn.closest('tr');
        const bookingId = row.dataset.id;
        
        try {
          // Get booking details from the row
          const booking = {
            id: bookingId,
            tracking_number: row.cells[1].textContent,
            status: row.cells[8].textContent.trim().toLowerCase()
          };
          
          openEditModal(booking, row);
        } catch (error) {
          console.error('Error:', error);
          showToast('Failed to load booking details', 'error');
        }
      });
    });
    
    // Add status classes to rows
    document.querySelectorAll('tr[data-id]').forEach(row => {
      const statusCell = row.cells[8]; // Status is now in the 9th column after adding No
      if (statusCell) {
        const status = statusCell.textContent.trim().toLowerCase();
        // Remove all status classes
        statusCell.className = statusCell.className.replace(/\bstatus-\S+/g, '');
        // Add the correct status class
        statusCell.classList.add(`status-${status}`);
      }
    });
  };

  // Initial load
  getCSRF().then(load);
  
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

  // Handle delete action
  document.addEventListener('click', async function(e) {
    const deleteBtn = e.target.closest('.delete-btn');
    if (!deleteBtn) return;
    
    e.preventDefault();
    const ok = await showConfirm('Are you sure you want to delete this booking?');
    if (!ok) return;
    
    const id = deleteBtn.dataset.id;
    const row = deleteBtn.closest('tr');
    
    try {
      if (!csrf) { await getCSRF(); }
      const response = await fetch('/APLX/backend/admin/booking_delete.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${encodeURIComponent(id)}&csrf=${encodeURIComponent(csrf)}`
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Fade out and remove the row
        row.style.opacity = '0.5';
        setTimeout(() => {
          row.remove();
          showToast('Booking deleted successfully', 'success');
        }, 300);
      } else {
        showToast(result.error || 'Failed to delete booking', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showToast('An error occurred while deleting the booking', 'error');
    }
  });
  
  // Show toast notification
  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
      <div class="toast-icon">${type === 'success' ? '✓' : '!'}</div>
      <div class="toast-message">${message}</div>
      <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Remove any existing toasts
    document.querySelectorAll('.toast').forEach(t => t.remove());
    
    // Add to DOM
    document.body.appendChild(toast);
    
    // Trigger reflow
    void toast.offsetWidth;
    
    // Show toast with animation
    toast.classList.add('show');
    
    // Hide after 3 seconds
    const hideTimeout = setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 5000);
    
    // Pause hide on hover
    toast.addEventListener('mouseenter', () => clearTimeout(hideTimeout));
    toast.addEventListener('mouseleave', () => {
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 1000);
    });
  }
  
  // Add toast styles
  const toastStyles = document.createElement('style');
  toastStyles.textContent = `
    .toast {
      position: fixed;
      bottom: 25px;
      right: 25px;
      background: #1e293b;
      color: white;
      padding: 16px 24px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 12px;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      transform: translateY(30px);
      opacity: 0;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 9999;
      max-width: 350px;
      border-left: 4px solid #3b82f6;
    }
    .toast.success {
      border-left-color: #10b981;
    }
    .toast.error {
      border-left-color: #ef4444;
    }
    .toast.show {
      transform: translateY(0);
      opacity: 1;
    }
    .toast-icon {
      font-size: 20px;
      flex-shrink: 0;
    }
    .toast-message {
      flex-grow: 1;
      line-height: 1.5;
    }
    .toast-close {
      background: none;
      border: none;
      color: #94a3b8;
      font-size: 20px;
      cursor: pointer;
      padding: 0;
      margin-left: 8px;
      line-height: 1;
      transition: color 0.2s;
    }
    .toast-close:hover {
      color: #e2e8f0;
    }
  `;
  document.head.appendChild(toastStyles);
})();
</script>
</body>
</html>




