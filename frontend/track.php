<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Track Shipment</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .track-page{ position:relative; padding:48px 0; min-height:calc(100vh - 80px); display:flex; align-items:center; }
    .track-page .container{ display:flex; justify-content:center; }
    .track-page::before{ content:""; position:absolute; inset:0; pointer-events:none;
      background:url('images/3d-characters-with-boxes-van-removebg-preview.png') center/cover no-repeat; opacity:.12; filter:saturate(.9); }
    .track-card{ position:relative; border:1px solid var(--border); border-radius:10px; padding:24px; box-shadow:0 10px 24px rgba(0,0,0,.25); width:100%; max-width:760px; }
    [data-theme="dark"] .track-card{ background:#0b3640; border-color:#09303a; }
    [data-theme="light"] .track-card{ background:#eef4ff; border-color:#d6e3ff; }
    .track-card h2{ margin:0 0 12px 0; font-size:clamp(22px,3vw,28px); font-weight:800; color:var(--text); }
    .track-form-row{ display:flex; gap:10px; }
    .track-form-row input{ flex:1; padding:12px; border-radius:10px; border:1px solid var(--border); background:#fff; color:#111; }
    .track-actions{ margin-top:12px; }
    .track-close{ position:absolute; top:10px; right:10px; background:#0f172a; color:#fff; border:1px solid var(--border); border-radius:10px; padding:6px 10px; cursor:pointer; font-weight:700; line-height:1; }
    [data-theme="light"] .track-close{ background:#111827; color:#fff; }
  </style>
</head>
<body>
<header class="navbar">
  <div class="container">
    <div class="brand"><span class="brand-icon" aria-hidden="true">🚚</span> Parcel Transport</div>
    <button id="themeToggle" class="theme-toggle centered" title="Toggle theme" aria-pressed="false">☀️/🌙</button>
    <nav>
      <a href="http://localhost/APLX/frontend/index.php">Home</a>
      <a href="http://localhost/APLX/frontend/track.php" class="active">Track</a>
      <a id="navBook" href="http://localhost/APLX/frontend/login.php?next=%2FAPLX%2Ffrontend%2Fcustomer%2Fbook.php">Book</a>
      <a href="http://localhost/APLX/frontend/login.php?stay=1" title="Login" aria-label="Login">👤</a>
    </nav>
  </div>
</header>
<main>
  <section class="track-page">
    <div class="container">
      <div class="track-card" id="trackCard">
        <button type="button" class="track-close" id="closeTrack" aria-label="Close form">✕</button>
        <h2>Track Shipment</h2>
        <form method="get" action="/APLX/backend/track_result.php">
          <div class="track-form-row">
            <input type="text" name="tn" placeholder="Enter Tracking Number" required>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>
<script>
// Dynamic Book link: require customer login
(function(){
  const book = document.getElementById('navBook');
  if (!book) return;
  const isLoggedIn = localStorage.getItem('isLoggedIn') === '1';
  const role = localStorage.getItem('userRole') || 'customer';
  const loggedCustomer = isLoggedIn && role === 'customer';
  book.href = loggedCustomer
    ? 'http://localhost/APLX/frontend/customer/book.php'
    : 'http://localhost/APLX/frontend/login.php?next=%2FAPLX%2Ffrontend%2Fcustomer%2Fbook.php';
})();
</script>
<script>
// Theme toggle + persist (same as index)
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
</script>
<script>
// Close track card -> go home
(function(){
  const btn = document.getElementById('closeTrack');
  if(btn){ btn.addEventListener('click', ()=>{ window.location.href = '/APLX/frontend/index.php'; }); }
})();
</script>
</body>
</html>


