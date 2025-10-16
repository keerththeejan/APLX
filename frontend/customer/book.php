<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Parcel Transport - Book</title>
  <link rel="stylesheet" href="../../css/style.css">
  <style>
    .booking-page{ position:relative; padding:40px 0; min-height:calc(100vh - 80px); display:flex; align-items:center; }
    .booking-page .container{ display:flex; justify-content:center; }
    .booking-page::before{ content:""; position:absolute; inset:0; pointer-events:none;
      background:url('../images/3d-characters-with-boxes-van-removebg-preview.png') center/cover no-repeat;
      opacity:.12; filter:saturate(.9); }
    .booking-card{ position:relative; border:1px solid var(--border); border-radius:8px; padding:22px; box-shadow:0 10px 24px rgba(0,0,0,.25); width:100%; max-width:980px; }
    .booking-close{ position:absolute; top:10px; right:10px; background:#0f172a; color:#fff; border:1px solid var(--border); border-radius:10px; padding:6px 10px; cursor:pointer; font-weight:700; line-height:1; }
    .booking-close:hover{ filter:brightness(1.05); }
    [data-theme="light"] .booking-close{ background:#111827; color:#fff; }
    /* Theme-specific card colors */
    [data-theme="dark"] .booking-card{ background:#0a2a33; border-color:#09303a; }
    [data-theme="light"] .booking-card{ background:#e6eeff; border-color:#c9dafd; }
    .booking-card h2{ margin:0 0 12px 0; font-size:clamp(22px,3vw,28px); font-weight:800; color:var(--text); }
    .booking-grid{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .booking-grid input, .booking-grid select, .booking-grid textarea{ width:100%; padding:12px; border-radius:10px; border:1px solid var(--border); }
    /* Inputs light theme */
    [data-theme="light"] .booking-grid input,
    [data-theme="light"] .booking-grid select,
    [data-theme="light"] .booking-grid textarea{ background:#ffffff; color:#0b1220; border-color:#c7d7ff; }
    [data-theme="light"] .booking-grid input:focus,
    [data-theme="light"] .booking-grid select:focus,
    [data-theme="light"] .booking-grid textarea:focus{ outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.25); }
    /* Inputs dark theme */
    [data-theme="dark"] .booking-grid input,
    [data-theme="dark"] .booking-grid select,
    [data-theme="dark"] .booking-grid textarea{ background:#0f1a33; color:#e2f3f6; border-color:#253049; }
    [data-theme="dark"] .booking-grid input::placeholder,
    [data-theme="dark"] .booking-grid textarea::placeholder{ color:#9fb4c0; }
    [data-theme="dark"] .booking-grid input:focus,
    [data-theme="dark"] .booking-grid select:focus,
    [data-theme="dark"] .booking-grid textarea:focus{ outline:none; border-color:#60a5fa; box-shadow:0 0 0 3px rgba(96,165,250,.25); }
    .booking-actions{ display:flex; gap:10px; margin-top:14px; }
    .btn-primary{ background:#2563eb; color:#fff; border:1px solid #1d4ed8; padding:10px 14px; border-radius:10px; cursor:pointer; font-weight:700; }
    .btn-secondary{ background:transparent; color:var(--text); border:1px solid var(--border); padding:10px 14px; border-radius:10px; cursor:pointer; }
    @media (max-width: 900px){ .booking-grid{ grid-template-columns:1fr; } }
    .popup-backdrop{ position:fixed; inset:0; background:rgba(0,0,0,.5); display:none; align-items:center; justify-content:center; z-index:1000; }
    .popup-card{ background:var(--bg); color:var(--text); border:1px solid var(--border); border-radius:10px; padding:18px; width:min(520px, 92vw); box-shadow:0 10px 24px rgba(0,0,0,.35); }
    .popup-actions{ display:flex; gap:10px; margin-top:14px; justify-content:flex-end; }
  </style>
  <link rel="icon" href="#">
  <meta name="theme-color" content="#0b1220">
  <meta name="description" content="Book your parcel shipment with Parcel Transport">
</head>
<body>
  <header class="navbar">
    <div class="container">
      <div class="brand"><span class="brand-icon" aria-hidden="true">🚚</span> Parcel Transport</div>
      <button id="themeToggle" class="theme-toggle centered" title="Toggle theme" aria-pressed="false">☀️/🌙</button>
      <nav>
        <a href="../index.php">Home</a>
        <a href="../track.php">Track</a>
        <a href="./book.php" class="active">Book</a>
        <a href="../login.php?stay=1" title="Login" aria-label="Login">👤</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="booking-page">
      <div class="container">
        <div class="booking-card" id="bookingCard">
          <button type="button" class="booking-close" id="closeBooking" aria-label="Close form">✕</button>
          <h2>Book Parcel</h2>
          <form id="bookForm" method="post" action="../../backend/book_submit.php">
            <div class="booking-grid">
              <input type="text" name="sender_name" placeholder="Sender Name" required>
              <input type="tel" name="sender_phone" placeholder="Sender Phone" required>
              <input type="text" name="receiver_name" placeholder="Receiver Name" required>
              <input type="tel" name="receiver_phone" placeholder="Receiver Phone" required>
              <input type="text" name="origin" placeholder="Origin City" required>
              <input type="text" name="destination" placeholder="Destination City" required>
              <input type="number" step="0.01" name="weight" placeholder="Weight (kg)" required>
            </div>
            <div class="booking-actions">
              <button class="btn-primary" type="submit">Book</button>
              <button class="btn-secondary" type="reset">Clear</button>
            </div>
          </form>
          <div class="popup-backdrop" id="bookPopup">
            <div class="popup-card">
              <h3 id="popupTitle" style="margin:0 0 8px 0; font-size:20px;">Booking Result</h3>
              <p id="popupMsg" style="margin:0 0 8px 0;"></p>
              <div id="popupActions" class="popup-actions"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
  // Auth guard: require backend session as customer to view this page
  (function(){
    const next = encodeURIComponent('/APLX/frontend/customer/book.php');
    fetch('/APLX/backend/whoami.php', { credentials: 'include' })
      .then(r => r.ok ? r.json() : Promise.reject())
      .then(data => {
        if (!data || !data.loggedIn || data.role !== 'customer') {
          window.location.replace('/APLX/frontend/login.php?next=' + next);
        }
      })
      .catch(() => {
        window.location.replace('/APLX/frontend/login.php?next=' + next);
      });
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
  // Close booking card: navigate to home page
  (function(){
    const btn = document.getElementById('closeBooking');
    if(btn){
      btn.addEventListener('click', ()=>{ window.location.href = '../index.php'; });
    }
  })();
  </script>
  <script>
  (function(){
    const form = document.getElementById('bookForm');
    const popup = document.getElementById('bookPopup');
    const msgEl = document.getElementById('popupMsg');
    const actions = document.getElementById('popupActions');
    function showPopup(message, tracking, details){
      if (details && details.ok){
        msgEl.innerHTML = `
          <div><strong>Tracking:</strong> ${details.tracking}</div>
          <div><strong>Status:</strong> ${details.status}</div>
          <div><strong>From:</strong> ${details.origin} → <strong>To:</strong> ${details.destination}</div>
          <div><strong>Receiver:</strong> ${details.receiver_name || ''}</div>
          <div><strong>Updated:</strong> ${details.updated_at || ''}</div>
          <hr style="margin:10px 0; opacity:.4">
          <div>${message}</div>`;
      } else {
        msgEl.textContent = message;
      }
      actions.innerHTML = '';
      const ok = document.createElement('button');
      ok.className = 'btn-primary';
      ok.type = 'button';
      ok.textContent = 'OK';
      ok.addEventListener('click', ()=>{ popup.style.display = 'none'; });
      actions.appendChild(ok);
      popup.style.display = 'flex';
    }
    if(form){
      form.addEventListener('submit', function(e){
        e.preventDefault();
        const fd = new FormData(form);
        fetch(form.action, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' }, credentials: 'include' })
          .then(r => r.ok ? r.json() : r.text().then(t=>{ throw new Error(t||'Error'); }))
          .then(data => {
            const msg = (data && data.message) ? data.message : 'Submitted';
            const tn = (data && data.tracking) ? data.tracking : '';
            if (data && data.ok){
              form.reset();
            }
            if (tn){
              fetch('/APLX/backend/track_result.php?tn=' + encodeURIComponent(tn), { headers: { 'Accept': 'application/json' }})
                .then(r => r.ok ? r.json() : Promise.resolve(null))
                .then(details => {
                  showPopup(msg, tn, details);
                })
                .catch(() => showPopup(msg, tn));
            } else {
              showPopup(msg, tn);
            }
          })
          .catch(()=>{
            showPopup('Something went wrong. Please try again.');
          });
      });
    }
  })();
  </script>
</body>
</html>




