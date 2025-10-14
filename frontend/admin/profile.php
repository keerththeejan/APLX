<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Profile</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .side-header{display:flex;align-items:center;gap:10px;padding:16px;border-bottom:1px solid var(--border)}
    .side-header .logo{font-size:22px}
    .side-header .app{font-weight:700}
    .sidebar nav{display:flex;flex-direction:column;padding:8px}
    .sidebar nav a{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px;margin:2px 8px;color:var(--muted);text-decoration:none}
    .sidebar nav a.active, .sidebar nav a:hover{background:#0f172a;color:var(--text)}
    .side-user{display:flex;gap:10px;align-items:center;padding:14px;border-top:1px solid var(--border)}
    .side-user .avatar{width:36px;height:36px;border-radius:999px;display:flex;align-items:center;justify-content:center;background:#111827}
    .content{padding:16px;margin-left:260px}
    .form-grid .form-row{ margin-bottom: 20px; }
    .form-grid .form-row.two{ display:flex; gap:20px; }
    .form-grid .form-row.two > *{ flex:1; }
    .form-grid label{ display:block; margin-bottom:8px; color:var(--muted); font-size:12px; }
    .form-grid .form-actions{ margin-top: 16px; }
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>
    <section class="card">
      <h2>Admin Profile</h2>
      <p class="muted">Update your details. Password change is optional.</p>
      <form id="adminProfileForm" class="form-grid" method="post" action="/APLX/backend/router.php?route=admin/profile/update">
        <div class="form-row">
          <label for="pf_name">Full Name</label>
          <input id="pf_name" type="text" name="name" placeholder="Full Name" required>
        </div>
        <div class="form-row two">
          <div style="flex:1">
            <label for="pf_email">Email</label>
            <input id="pf_email" type="email" name="email" placeholder="Email" required>
          </div>
          <div style="flex:1">
            <label for="pf_phone">Phone</label>
            <input id="pf_phone" type="tel" name="phone" placeholder="Phone">
          </div>
        </div>
        <div class="form-row two">
          <div style="flex:1">
            <label for="pf_pass">New Password</label>
            <input id="pf_pass" type="password" name="password" placeholder="New Password">
          </div>
          <div style="flex:1">
            <label for="pf_confirm">Confirm Password</label>
            <input id="pf_confirm" type="password" name="confirm_password" placeholder="Confirm Password">
          </div>
        </div>
        <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end">
          <a class="btn btn-secondary" href="/APLX/frontend/admin/dashboard.php">Cancel</a>
          <button class="btn" type="submit">Save Changes</button>
        </div>
      </form>
      <div id="adminProfileStatus" class="inline-status" aria-live="polite" style="margin-top:16px"></div>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
<script>
  (function(){
    const form = document.getElementById('adminProfileForm');
    const status = document.getElementById('adminProfileStatus');
    function setStatus(msg, ok=true){ if(!status) return; status.textContent = msg; status.style.color = ok ? '#22c55e' : '#ef4444'; }
    async function load(){
      try{
        const res = await fetch('/APLX/backend/router.php?route=admin/profile/get', { cache:'no-store' });
        const data = await res.json();
        const it = data.item||{};
        document.getElementById('pf_name').value = it.name||'';
        document.getElementById('pf_email').value = it.email||'';
        document.getElementById('pf_phone').value = it.phone||'';
      }catch(e){ /* ignore */ }
    }
    load();

    form?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      setStatus('Saving...', true);
      try{
        const fd = new FormData(form);
        const res = await fetch(form.action, { method:'POST', body: fd });
        const data = await res.json();
        if (data.ok){
          setStatus('Saved successfully.', true);
          document.getElementById('pf_pass').value = '';
          document.getElementById('pf_confirm').value = '';
        } else {
          setStatus(data.error||'Failed to save', false);
        }
      }catch(err){ setStatus('Failed to save', false); }
    });
  })();
</script>
</body>
</html>
