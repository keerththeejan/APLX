<?php
 header('Location: /APLX/frontend/index.php', true, 302);
 exit;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Customer Registration</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .register-page{ position:relative; padding:48px 0; min-height:calc(100vh - 80px); display:flex; align-items:center; }
    .register-page .container{ display:flex; justify-content:center; }
    .register-page::before{ content:""; position:absolute; inset:0; pointer-events:none;
      background:url('../images/3d-characters-with-boxes-van-removebg-preview.png') center/cover no-repeat; opacity:.12; filter:saturate(.9); }
    .register-card{ position:relative; border:1px solid var(--border); border-radius:10px; padding:26px; box-shadow:0 10px 24px rgba(0,0,0,.25); width:100%; max-width:1080px; }
    /* Dark blue in dark theme, light card in light theme */
    [data-theme="dark"] .register-card{ background:#0b3640; border-color:#09303a; }
    [data-theme="light"] .register-card{ background:#eef4ff; border-color:#d6e3ff; }
    .register-card h2{ margin:0 0 12px 0; font-size:clamp(22px,3vw,28px); font-weight:800; color:var(--text); }
    .register-grid{ display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:14px; }
    .register-grid input{ width:100%; padding:12px 14px; border-radius:10px; border:1px solid var(--border); font-size:14px; outline:none; transition:border-color .15s, box-shadow .15s, background-color .15s, color .15s; }
    /* Theme-aware input colors */
    [data-theme="dark"] .register-grid input{ background:#0b1220; color:#e2f3f6; }
    [data-theme="light"] .register-grid input{ background:#ffffff; color:#111827; border-color:#c9dafd; }
    .register-grid input::placeholder{ color:#94a3b8 }
    [data-theme="light"] .register-grid input::placeholder{ color:#475569 }
    .register-grid input:focus{ border-color:#60a5fa; box-shadow:0 0 0 3px rgba(59,130,246,.25); }
    .input-with-icon{ position:relative; }
    .input-with-icon input{ padding-right:44px; }
    .toggle-eye{ position:absolute; right:10px; top:50%; transform:translateY(-50%); border:1px solid var(--border); background:transparent; cursor:pointer; font-size:18px; line-height:1; color:#94a3b8; border-radius:8px; padding:4px 6px; z-index:2; transition:color .15s, border-color .15s, background-color .15s; pointer-events:auto; }
    .toggle-eye:hover{ color:#0ea5e9; border-color:#60a5fa; }
    [data-theme="light"] .toggle-eye{ color:#0b1220; border-color:#c9dafd; background:#eef5ff }
    [data-theme="light"] .toggle-eye:hover{ color:#2563eb; }
    .toggle-eye[aria-pressed="true"]{ color:#16a34a; }
    .toggle-eye:focus{ outline:2px solid #60a5fa; border-radius:6px; }
    @media (max-width: 900px){ .register-grid{ grid-template-columns:1fr; } }
    .reg-actions{ margin-top:16px; display:flex; gap:12px; justify-content:flex-end; }
    .btn{ padding:10px 16px; border-radius:10px; cursor:pointer; font-weight:800; border:1px solid transparent; }
    .btn-primary{ background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; border-color:#16a34a; box-shadow:0 6px 14px rgba(22,163,74,.25); }
    .btn-primary:hover{ filter:brightness(1.05); }
    .btn-danger{ background:#ef4444; color:#fff; border-color:#dc2626; box-shadow:0 6px 14px rgba(239,68,68,.25); }
    .btn-danger:hover{ filter:brightness(1.05); }
    .register-close{ position:absolute; top:10px; right:10px; background:#0f172a; color:#fff; border:1px solid var(--border); border-radius:10px; padding:6px 10px; cursor:pointer; font-weight:700; line-height:1; z-index:5; }
    [data-theme="light"] .register-close{ background:#111827; color:#fff; }
    .reg-status{ margin:10px 0 0 0; padding:10px 12px; border-radius:10px; font-weight:700; }
    /* Explicit green/red text with soft backgrounds */
    .reg-ok{ background:#ecfdf5; color:#16a34a; border:1px solid #86efac; }
    .reg-fail{ background:#fef2f2; color:#ef4444; border:1px solid #fecaca; }
  </style>
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand"><span class="brand-icon" aria-hidden="true">🚚</span> Parcel Transport</div>
    <button id="themeToggle" class="theme-toggle centered" title="Toggle theme" aria-pressed="false">☀️/🌙</button>
    <nav>
      <a href="http://localhost/APLX/frontend/index.php">Home</a>
      <a href="http://localhost/APLX/frontend/track.php">Track</a>
      <a id="navBook" href="http://localhost/APLX/frontend/login.php?next=%2FParcel%2Ffrontend%2Fcustomer%2Fbook.php">Book</a>
      <a href="http://localhost/APLX/frontend/customer/register.php" class="active">Register</a>
      <a href="http://localhost/APLX/frontend/login.php?stay=1" title="Login" aria-label="Login">👤</a>
    </nav>
  </div>
</header>

<main>
  <section class="register-page">
    <div class="container">
      <div class="register-card" id="registerCard">
        <a href="/APLX/frontend/index.php" class="register-close" id="closeRegister" role="button" aria-label="Close form">✕</a>
        <h2>Create Customer Account</h2>
        <form method="post" action="/APLX/backend/customer_register.php">
          <div class="register-grid">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <div class="input-with-icon">
              <input type="password" name="password" id="password" placeholder="Password" required>
              <button type="button" id="togglePassword" class="toggle-eye" aria-label="Show/Hide Password" aria-pressed="false" title="Show/Hide Password" tabindex="0">👁️</button>
            </div>
            <input type="text" name="phone" placeholder="Phone" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="text" name="district" list="districts" placeholder="District (searchable)" aria-label="District" required>
            <input type="text" name="province" list="provinces" placeholder="Province (searchable)" aria-label="Province" required>
          </div>
          <datalist id="provinces">
            <option value="Western"></option>
            <option value="Central"></option>
            <option value="Southern"></option>
            <option value="Northern"></option>
            <option value="Eastern"></option>
            <option value="North Western"></option>
            <option value="North Central"></option>
            <option value="Uva"></option>
            <option value="Sabaragamuwa"></option>
          </datalist>
          <datalist id="districts">
            <option value="Colombo"></option>
            <option value="Gampaha"></option>
            <option value="Kalutara"></option>
            <option value="Kandy"></option>
            <option value="Matale"></option>
            <option value="Nuwara Eliya"></option>
            <option value="Galle"></option>
            <option value="Matara"></option>
            <option value="Hambantota"></option>
            <option value="Jaffna"></option>
            <option value="Kilinochchi"></option>
            <option value="Mannar"></option>
            <option value="Vavuniya"></option>
            <option value="Mullaitivu"></option>
            <option value="Batticaloa"></option>
            <option value="Ampara"></option>
            <option value="Trincomalee"></option>
            <option value="Kurunegala"></option>
            <option value="Puttalam"></option>
            <option value="Anuradhapura"></option>
            <option value="Polonnaruwa"></option>
            <option value="Badulla"></option>
            <option value="Monaragala"></option>
            <option value="Ratnapura"></option>
            <option value="Kegalle"></option>
          </datalist>
          <div class="reg-actions">
            <button class="btn btn-primary" type="submit">Register</button>
            <button class="btn btn-danger" type="reset">Clear</button>
          </div>
        </form>
        <div id="regStatus" class="reg-status" style="display:none" role="status" aria-live="polite"></div>
      </div>
    </div>
  </section>
</main>
<script>
// Theme toggle + persist (hardened)
(function(){
  const btn = document.getElementById('themeToggle');
  const saved = localStorage.getItem('theme');
  if (saved) document.documentElement.setAttribute('data-theme', saved);
  function updateVisual(){
    const cur = document.documentElement.getAttribute('data-theme') || 'dark';
    if (btn) {
      btn.textContent = cur === 'light' ? '☀️' : '🌙';
      btn.setAttribute('aria-pressed', String(cur !== 'light'));
      btn.setAttribute('aria-label', cur === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
    }
  }
  function setTheme(t){ document.documentElement.setAttribute('data-theme', t); localStorage.setItem('theme', t); updateVisual(); }
  updateVisual();
  function toggle(){ const cur = document.documentElement.getAttribute('data-theme') || 'dark'; setTheme(cur === 'light' ? 'dark' : 'light'); }
  btn?.addEventListener('click', toggle);
  btn?.addEventListener('keydown', (e)=>{ if(e.key==='Enter' || e.key===' '){ e.preventDefault(); toggle(); } });
  btn?.addEventListener('touchstart', (e)=>{ e.preventDefault(); toggle(); }, { passive:false });
  btn?.addEventListener('pointerdown', (e)=>{ if(e.pointerType==='pen'||e.pointerType==='touch'){ e.preventDefault(); } });
})();
// Dynamic Book link: require customer login
(function(){
  const book = document.getElementById('navBook');
  if (!book) return;
  const isLoggedIn = localStorage.getItem('isLoggedIn') === '1';
  const role = localStorage.getItem('userRole') || 'customer';
  const loggedCustomer = isLoggedIn && role === 'customer';
  book.href = loggedCustomer
    ? 'http://localhost/APLX/frontend/customer/book.php'
    : 'http://localhost/APLX/frontend/login.php?next=%2FAPLX%2FParcel%2Ffrontend%2Fcustomer%2Fbook.php';
})();
// Toggle password visibility (hardened)
(function(){
  const input = document.getElementById('password');
  const btn = document.getElementById('togglePassword');
  if(!input || !btn) return;
  // Ensure correct button type
  if (!btn.getAttribute('type')) btn.setAttribute('type','button');
  const toggle = (e)=>{
    if(e){ e.preventDefault(); e.stopPropagation(); }
    const start = input.selectionStart, end = input.selectionEnd;
    const isPassword = (input.getAttribute('type') || input.type) === 'password';
    input.setAttribute('type', isPassword ? 'text' : 'password');
    // Restore caret/selection and focus
    setTimeout(()=>{
      input.focus();
      try { if(start !== null && end !== null) input.setSelectionRange(start, end); } catch(_) {}
    }, 0);
    btn.setAttribute('aria-pressed', String(isPassword));
    btn.textContent = isPassword ? '🙈' : '👁️';
  };
  btn.addEventListener('mousedown', (e)=>{ e.preventDefault(); });
  btn.addEventListener('click', toggle);
  btn.addEventListener('touchstart', toggle, {passive:false});
  btn.addEventListener('keydown', (e)=>{ if(e.key==='Enter' || e.key===' '){ toggle(e); } });
})();
// Close -> go home
(function(){
  const closeBtn = document.getElementById('closeRegister');
  if(!closeBtn) return;
  const goHome = (e)=>{ e.preventDefault(); e.stopPropagation(); window.location.assign('/APLX/frontend/index.php'); };
  closeBtn.addEventListener('click', goHome);
  closeBtn.addEventListener('touchstart', goHome, {passive:false});
})();
// Show registration status (success/error) from query params
(function(){
  const params = new URLSearchParams(window.location.search);
  const status = params.get('status'); // 'ok' or 'error'
  const msg = params.get('msg');
  const box = document.getElementById('regStatus');
  if (!box) return;
  if (status && msg) {
    box.textContent = msg;
    box.classList.remove('reg-ok','reg-fail');
    if (status === 'ok') box.classList.add('reg-ok');
    else box.classList.add('reg-fail');
    box.style.display = '';
    // Clean the URL so message doesn't persist on refresh
    const cleanUrl = window.location.origin + window.location.pathname;
    window.history.replaceState({}, document.title, cleanUrl);
  }
})();




