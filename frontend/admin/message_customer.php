<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Message Customer</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    /* Lock page: remove scrollbar and keep fixed */
    html, body { height:100%; overflow:hidden; }
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px; overflow:hidden}
    /* Big visual placeholder for message typing step */
    .message-placeholder{min-height:140px;border:1px dashed var(--border);border-radius:10px;padding:12px;display:flex;align-items:center;justify-content:center;color:var(--muted);background:#0b1220;font-size:13px}
    [data-theme="light"] .message-placeholder{background:#f3f4f6}
    /* Spacing and input/placeholder theming */
    .form-grid .form-row{ margin-bottom: 14px; }
    .input-like{ width:100%; padding:12px; border-radius:10px; border:1px solid var(--border); background:#0b1220; color:var(--text); }
    .input-like[readonly], .input-like:disabled{ opacity:.9 }
    [data-theme="light"] .input-like{ background:#ffffff; color:#111; }
    /* Yellow title styling */
    .title-yellow{ color:#facc15; /* amber-400 */ }
    /* Green helper text */
    .help-green{ color:#22c55e; }
  </style>
</head>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>
    <section class="card">
      <h2 class="title-yellow">Message Customer</h2>
      <p class="muted help-green">Start by selecting a customer by name. The Customer Email will auto-fill. You will type the actual message on the next step.</p>
      <form id="msgCustForm" class="form-grid" method="get" action="/APLX/backend/admin/message_customer.php">
        <!-- Hidden field actually sent to backend -->
        <input type="hidden" id="mc_customer_id" name="customer_id" />

        <div class="form-row">
          <label for="mc_customer_name">Customer Name</label>
          <input id="mc_customer_name" type="text" name="customer_name" placeholder="Type customer name" list="custList" autocomplete="off" required class="input-like">
          <datalist id="custList"></datalist>
        </div>
        <div class="form-row">
          <label for="mc_customer_email">Customer Email (auto)</label>
          <input id="mc_customer_email" type="email" name="customer_email" placeholder="Auto-filled" readonly class="input-like">
        </div>
        <div class="form-row">
          <label for="mc_subject">Subject</label>
          <input id="mc_subject" type="text" name="subject" placeholder="Subject" required class="input-like">
        </div>
        <div class="form-row">
          <label>Message</label>
          <textarea id="mc_message" name="message" rows="8" placeholder="Type your message..." required class="input-like" style="min-height:140px; resize:vertical"></textarea>
        </div>
        <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end">
          <a class="btn btn-secondary" href="/APLX/frontend/admin/dashboard.php">Cancel</a>
          <button class="btn" type="submit">Continue to Send</button>
        </div>
      <div class="muted" style="margin-top:10px">Alternatively, you can open the full backend page: <a class="btn btn-sm btn-outline" href="/APLX/backend/admin/message_customer.php">Open Live</a></div>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
<script>
  // Lightweight autocomplete for Customer Name -> fills ID and Email
  (function(){
    const nameInput = document.getElementById('mc_customer_name');
    const idInput = document.getElementById('mc_customer_id');
    const idDisplay = document.getElementById('mc_customer_id_display');
    const emailInput = document.getElementById('mc_customer_email');
    const list = document.getElementById('custList');
    const form = document.getElementById('msgCustForm');
    const cache = new Map(); // key: option value text, val: {id, name, email}
    let lastItems = [];
    let t = null;
    function debounce(fn, ms){ clearTimeout(t); t = setTimeout(fn, ms); }

    async function searchCustomers(q){
      if(!q || q.trim().length < 2){ list.innerHTML = ''; return; }
      try{
        const params = new URLSearchParams({ q:q.trim(), limit:'10', page:'1' });
        const res = await fetch('/APLX/backend/admin/customer_lookup.php?'+params.toString(), { cache:'no-store' });
        if(!res.ok) return;
        const data = await res.json();
        const items = (data.items||[]);
        cache.clear();
        list.innerHTML='';
        lastItems = items;
        items.forEach(it => {
          const label = `${it.name||''} — ${it.email||''}`.trim();
          const opt = document.createElement('option');
          opt.value = label;
          list.appendChild(opt);
          cache.set(label.toLowerCase(), { id: it.id, name: it.name, email: it.email });
        });
        // After refreshing suggestions, try to auto-fill from current query
        fillByQuery(nameInput.value||'');
      }catch(e){ /* silent */ }
    }

    function fillByQuery(q){
      const query = (q||'').toLowerCase().trim();
      if (!query){ clearAuto(); return; }
      // Best match: startsWith on name/email, else includes
      let hit = lastItems.find(it => (it.name||'').toLowerCase().startsWith(query) || (it.email||'').toLowerCase().startsWith(query));
      if (!hit) hit = lastItems.find(it => (it.name||'').toLowerCase().includes(query) || (it.email||'').toLowerCase().includes(query));
      if (hit){ setAuto(hit); } else { clearAuto(); }
    }

    function setAuto(hit){
      // Hidden field holds actual id for submission
      idInput.value = String(hit.id||'');
      // Show info as placeholders (email only visible)
      if (idDisplay){ idDisplay.value = String(hit.id||''); idDisplay.placeholder = 'Auto-filled'; }
      if (emailInput){ emailInput.value = ''; emailInput.placeholder = hit.email || 'Auto-filled'; }
      const subj = document.getElementById('mc_subject');
      if(subj && !subj.value){ subj.placeholder = `Subject for ${hit.name||'customer'}`; }
    }

    function clearAuto(){
      idInput.value = '';
      if (idDisplay){ idDisplay.value = ''; idDisplay.placeholder = 'Auto-filled'; }
      if (emailInput){ emailInput.value = ''; emailInput.placeholder = 'Auto-filled'; }
    }

    function applySelection(){ fillByQuery(nameInput.value||''); }

    nameInput?.addEventListener('input', ()=> { debounce(()=> searchCustomers(nameInput.value), 250); applySelection(); });
    nameInput?.addEventListener('change', applySelection);
    nameInput?.addEventListener('blur', applySelection);

    form?.addEventListener('submit', (e)=>{
      // Require a resolved customer_id
      if(!idInput.value){
        e.preventDefault();
        nameInput.focus();
        nameInput.setCustomValidity('Please choose a customer from the suggestions');
        nameInput.reportValidity();
        setTimeout(()=> nameInput.setCustomValidity(''), 1500);
      }
    });
  })();
  </script>
</body>
</html>




