<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Contact</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .two-col{display:grid;grid-template-columns:1fr;gap:16px}
    .card input[type="text"],
    .card input[type="email"],
    .card input[type="tel"],
    .card textarea,
    .card select{background:#0b1220;border:1px solid var(--border);color:var(--text);border-radius:8px;padding:10px;width:100%}
    .card input::placeholder,.card textarea::placeholder{color:var(--muted)}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
  <div id="topbar"></div>
  <div class="page-actions" style="text-align:right; margin:50px 0 12px;">
    <a class="btn btn-outline" href="/APLX/frontend/admin/settings.php" title="Back to Settings">← Back to Settings</a>
  </div>
  <section class="card">
    <h2>Website Contact Details</h2>
    <form id="siteContactForm" class="stack" method="post" action="/APLX/backend/admin/site_contact_api.php">
      <input type="hidden" name="csrf" id="csrfField" value="">
      <div class="grid">
        <label style="grid-column:1/-1">Address
          <input type="text" name="address" id="scAddress" placeholder="Ariviyal Nagar, Kilinochchi, Sri Lanka" required>
        </label>
        <label>Phone
          <input type="tel" name="phone" id="scPhone" placeholder="+94 21 492 7799" required>
        </label>
        <label>Email
          <input type="email" name="email" id="scEmail" placeholder="info@slgti.com" required>
        </label>
        <label style="grid-column:1/-1">Weekday Hours
          <input type="text" name="hours_weekday" id="scWeekday" placeholder="Mon - Fri: 8:30 AM - 4:15 PM">
        </label>
        <label>Saturday Hours
          <input type="text" name="hours_sat" id="scSat" placeholder="Sat: 9:00 AM - 2:00 PM">
        </label>
        <label>Sunday Hours
          <input type="text" name="hours_sun" id="scSun" placeholder="Sun: Closed">
        </label>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
        <button class="btn" type="submit">Save</button>
        <button class="btn btn-outline" id="scReload" type="button">Reload</button>
        <a class="btn btn-outline" href="/APLX/frontend/index.php">View Site</a>
      </div>
      <div id="scStatus" class="muted" style="margin-top:6px" aria-live="polite"></div>
    </form>
  </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
<script>
// Load + Save site contact
(function(){
  const form = document.getElementById('siteContactForm');
  const statusEl = document.getElementById('scStatus');
  const csrfField = document.getElementById('csrfField');
  const btnReload = document.getElementById('scReload');
  const f = {
    address: document.getElementById('scAddress'),
    phone: document.getElementById('scPhone'),
    email: document.getElementById('scEmail'),
    weekday: document.getElementById('scWeekday'),
    sat: document.getElementById('scSat'),
    sun: document.getElementById('scSun')
  };
  async function getCSRF(){
    try{ const r = await fetch('/APLX/backend/admin/site_contact_api.php?action=csrf',{cache:'no-store'}); if(r.ok){ const d=await r.json(); csrfField.value=d.csrf||''; } }catch(_){ }
  }
  async function load(){
    statusEl.textContent = 'Loading...';
    try{
      const r = await fetch('/APLX/backend/site_contact.php',{cache:'no-store'});
      const d = r.ok ? await r.json() : { item:{} };
      const it = d.item||{};
      f.address.value = it.address||'';
      f.phone.value = it.phone||'';
      f.email.value = it.email||'';
      f.weekday.value = it.hours_weekday||'';
      f.sat.value = it.hours_sat||'';
      f.sun.value = it.hours_sun||'';
      statusEl.textContent = '';
    }catch(e){ statusEl.textContent='Load failed'; }
  }
  form?.addEventListener('submit', async (e)=>{
    e.preventDefault(); statusEl.textContent='Saving...';
    const fd = new FormData(form);
    try{
      const r = await fetch(form.action,{ method:'POST', body: fd });
      if (!r.ok) throw new Error('HTTP '+r.status);
      statusEl.textContent='Saved';
      await getCSRF();
    }catch(e){ statusEl.textContent='Save failed'; }
  });
  btnReload?.addEventListener('click', load);
  getCSRF().then(load);
})();
</script>
</body>
</html>





