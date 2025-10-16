<?php
$root = '/APLX/';
if (php_sapi_name() !== 'cli') {
    $isDirect = isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === 'login.php';
    if ($isDirect) {
        $params = [];
        if (isset($_GET['next'])) { $params['next'] = $_GET['next']; }
        if (isset($_GET['stay'])) { $params['stay'] = $_GET['stay']; }
        $params['login'] = '1';
        setcookie('show_login', '1', time() + 120, '/APLX');
        header('Location: ' . $root . (count($params) ? ('?' . http_build_query($params)) : ''), true, 302);
        exit;
    }
}
$showError = (isset($_GET['status']) && strtolower($_GET['status']) === 'error');
if ($showError && !headers_sent()) {
    http_response_code(401);
    header('X-Login-Error: 1');
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    :root{ --login-teal:#0b3640; --login-teal-dark:#09303a; --login-ink:#e2f3f6; --login-border:#0f2d35 }
    /* Page-level light background and center layout */
    body.login-page{background:#f6f9ff;color:#0b1220;position:relative;min-height:100vh;overflow:hidden}
    /* Use local background image */
    body.login-page::before{content:"";position:fixed;inset:0;background-image:url('images/pngtree-truck-delivering-packages-across-a-3d-world-map-picture-image_3756058.jpg');background-size:cover;background-position:center;opacity:.12;pointer-events:none;z-index:0;filter:saturate(.9)}
    .login-center{min-height:calc(100vh - 80px);display:block}
    .login-card{max-width:520px;margin:32px auto}
    .role-switch{display:flex;gap:8px;margin-top:8px}
    /* Clearer buttons on light bg */
    .role-switch button{padding:8px 12px;border-radius:10px;border:1px solid var(--login-border);background:var(--login-teal-dark);color:var(--login-ink);cursor:pointer}
    .role-switch button.active{outline:2px solid #3b82f6}
    /* Close button inside the card */
    .login-card .card{position:relative}
    .login-close{position:absolute;top:10px;right:10px;background:#0f172a;color:#fff;border:1px solid var(--border);border-radius:10px;padding:6px 10px;cursor:pointer;font-weight:700;line-height:1}
    .login-close:hover{filter:brightness(1.05)}
    [data-theme="light"] .login-close{background:#111827;color:#fff}
    /* Card uses image-matched dark teal in all themes for consistency */
    .login-card .card{background:var(--login-teal);border:1px solid var(--login-border);box-shadow:0 12px 28px rgba(15,23,42,.22);border-radius:14px;color:var(--login-ink)}
    /* Inputs comfortable spacing and clear placeholder */
    #commonLoginForm input{padding:12px 14px;border-radius:10px;background:var(--login-teal-dark);border:1px solid var(--login-border);color:var(--login-ink)}
    #commonLoginForm input::placeholder{letter-spacing:.3px;color:#9fb4c0}
    /* Full-screen stage to center the form */
    .login-stage{position:fixed;inset:0;display:grid;place-items:center;z-index:1;padding:16px}
    /* Centered card inside the stage */
    .login-fixed-card{position:relative;width:100%;max-width:560px}
    .login-fixed-card h2{color:var(--login-ink)}
    .login-fixed-card .muted{color:#cde7ec}
    /* Form spacing for inputs and button */
    #commonLoginForm{display:grid;gap:12px;margin-top:14px}
    #commonLoginForm .btn{padding:8px 12px; font-size:14px; line-height:1.2; border-radius:8px; width:fit-content; align-self:start}
    /* Password eye toggle */
    .input-wrap{position:relative}
    .toggle-pass{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:1px solid var(--login-border);color:var(--login-ink);border-radius:8px;padding:6px 8px;cursor:pointer}
    .toggle-pass:hover{filter:brightness(1.05)}
    [data-theme="light"] .toggle-pass{border-color:#c9dafd;color:#0b1220}
    /* Dark theme: keep same palette and dim background for contrast */
    [data-theme="dark"] body.login-page{ background:#0b1220; color:#e2f3f6; }
    [data-theme="dark"] body.login-page::before{ opacity:.22; filter:saturate(.8) brightness(.5); }
    [data-theme="dark"] .role-switch button{background:var(--login-teal-dark);color:var(--login-ink);border:1px solid var(--login-border)}
    [data-theme="dark"] .login-card .card{background:var(--login-teal);border:1px solid var(--login-border);color:var(--login-ink);box-shadow:0 12px 28px rgba(0,0,0,.45)}
    [data-theme="dark"] #commonLoginForm input{background:var(--login-teal-dark);border:1px solid var(--login-border);color:var(--login-ink)}
    [data-theme="dark"] #commonLoginForm input::placeholder{color:#9fb4c0}
    [data-theme="dark"] .login-fixed-card h2{color:var(--login-ink)}
    [data-theme="dark"] .login-fixed-card .muted{color:#cde7ec}

    /* Light theme: light card and inputs for clarity */
    [data-theme="light"] body.login-page{ background:#f6f9ff; color:#0b1220; }
    [data-theme="light"] body.login-page::before{ opacity:.10; filter:saturate(.95) brightness(1); }
    /* Match the sample light blue */
    [data-theme="light"] .login-card .card{ background:#eaf2ff; border:1px solid #c9dafd; color:#0b1220; box-shadow:0 12px 28px rgba(15,23,42,.10); }
    [data-theme="light"] #commonLoginForm input{ background:#eef5ff; border:1px solid #c9dafd; color:#0b1220; }
    [data-theme="light"] #commonLoginForm input::placeholder{ color:#475569; }
    [data-theme="light"] .role-switch button{ background:#ffffff; color:#0b1220; border:1px solid #c9dafd; }
    [data-theme="light"] .login-fixed-card h2{ color:#0b1220; }
    [data-theme="light"] .login-fixed-card .muted{ color:#475569; }
  </style>
<body class="login-page">
<header class="navbar">
  <div class="container">
    <div class="brand"><span class="brand-icon" aria-hidden="true">🚚</span> Parcel Transport</div>
    <button id="themeToggle" class="theme-toggle centered" title="Toggle theme" aria-pressed="false">☀️/🌙</button>
    <nav>
      <a href="index.php">Home</a>
      <a href="track.php" class="active">Track</a>
      <a href="customer/book.php">Book</a>
      <a href="/APLX/?login=1" title="Login" aria-label="Login">👤</a>
    </nav>
  </div>
</header>
<main class="container small login-card login-center">
  <div class="login-stage">
    <section class="card login-fixed-card">
    <button type="button" class="login-close" id="closeLogin" aria-label="Close" onclick="window.location.href='index.php'">✕</button>
    <h2>Login</h2>
    <p class="muted">Choose role and enter credentials.</p>
    <p id="loginError" style="margin:8px 0 0;color:#fca5a5;font-weight:600; display: <?php echo $showError ? 'block' : 'none'; ?>;">Invalid email or password.</p>
    <div class="role-switch" role="tablist" aria-label="Select role">
      <button id="asCustomer" class="active" role="tab" aria-selected="true">Customer</button>
      <button id="asAdmin" role="tab" aria-selected="false">Admin</button>
    </div>
    <form id="commonLoginForm" style="margin-top:12px" method="post" action="/APLX/backend/auth_login.php">
      <input type="hidden" name="role" id="roleField" value="customer">
      <input type="hidden" name="next" id="nextField" value="">
      <input id="loginEmail" name="email" type="email" placeholder="Email" required>
      <div class="input-wrap">
        <input id="loginPass" name="password" type="password" placeholder="Password" required aria-describedby="togglePass">
        <button type="button" id="togglePass" class="toggle-pass" aria-label="Show password" title="Show/Hide password">👁</button>
      </div>
      <button class="btn" type="submit">Login</button>
    </form>
    </section>
  </div>
</main>
<script>
// Theme toggle + persist
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
  btn?.addEventListener('click', ()=>{
    const cur = document.documentElement.getAttribute('data-theme') || 'dark';
    setTheme(cur === 'light' ? 'dark' : 'light');
  });
})();
// Common login (frontend-only)
(function(){
  const btnCust = document.getElementById('asCustomer');
  const btnAdmin = document.getElementById('asAdmin');
  const roleField = document.getElementById('roleField');
  const nextField = document.getElementById('nextField');
  let role = 'customer';
  // read next param
  const params = new URLSearchParams(location.search);
  const next = params.get('next');
  const status = params.get('status');
  const stay = params.get('stay') === '1';
  if (next) nextField.value = next;
  if (status === 'error') {
    const el = document.getElementById('loginError');
    if (el) el.style.display = 'block';
  }
  // If next points to admin pages, default to admin role
  if (next && /\/APLX\/frontend\/admin\//i.test(next)) {
    role = 'admin';
  }
  function setRole(r){ role = r; roleField.value = r; btnCust.classList.toggle('active', r==='customer'); btnCust.setAttribute('aria-selected', String(r==='customer')); btnAdmin.classList.toggle('active', r==='admin'); btnAdmin.setAttribute('aria-selected', String(r==='admin')); }
  btnCust?.addEventListener('click', ()=> setRole('customer'));
  btnAdmin?.addEventListener('click', ()=> setRole('admin'));
  // Reflect initial role in UI
  setRole(role);
  // Password toggle
  const passInput = document.getElementById('loginPass');
  const toggleBtn = document.getElementById('togglePass');
  toggleBtn?.addEventListener('click', ()=>{
    if (!passInput) return;
    const isHidden = passInput.type === 'password';
    passInput.type = isHidden ? 'text' : 'password';
    toggleBtn.textContent = isHidden ? '🙈' : '👁';
    toggleBtn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    toggleBtn.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
  });
  const form = document.getElementById('commonLoginForm');
  form?.addEventListener('submit', ()=>{
    // Ensure hidden role field matches current tab before submit
    try{
      const custActive = btnCust?.classList.contains('active');
      const adminActive = btnAdmin?.classList.contains('active');
      if (adminActive) setRole('admin'); else if (custActive) setRole('customer');
    }catch(_){/* ignore */}
    // Allow normal submit to backend which will handle redirects
  });
  // Close button -> back to previous or home
  const closeBtn = document.getElementById('closeLogin');
  closeBtn?.addEventListener('click', ()=>{
    window.location.href = 'index.php';
  });
  // No frontend fast-path; backend session decides
})();
</script>
</body>
</html>




\
\
