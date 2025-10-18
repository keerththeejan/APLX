<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Dashboard</title>
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
    .content{padding:16px;margin-left:260px; padding-top: 100px;}
    .icon{width:18px;display:inline-flex;align-items:center;justify-content:center}
    .topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
    .topbar .right{display:flex;align-items:center;gap:12px}
    .clock{background:#0b1220;border:1px solid var(--border);padding:8px 12px;border-radius:12px;color:var(--muted);font-size:12px;text-align:right;min-width:180px}
    .icon-btn{background:#0b1220;border:1px solid var(--border);padding:8px 10px;border-radius:10px;cursor:pointer}
    .icon-btn:hover{background:#111827}
    .hamburger{background:transparent;border:2px solid transparent;color:#ffffff;padding:10px;border-radius:10px;box-shadow:none;font-size:26px;line-height:1;cursor:pointer;transition:border-color .15s ease, filter .15s ease}
    .hamburger:hover{background:transparent;filter:brightness(1.1);border-color:#22c55e}
    .hamburger:focus{outline:none;border-color:#22c55e}
    .notif{position:relative}
    .notif::after{content:"2";position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:999px;padding:2px 6px;font-size:10px;line-height:1}
    .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;margin:8px 0 16px 0}
    .stat .stat-title{color:var(--muted);margin:0}
    .stat .stat-value{font-size:28px;font-weight:800}
    .stat-row{display:flex;align-items:center;gap:12px}
    .stat-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border)}
    .stat-icon svg{width:22px;height:22px;stroke:#fff;stroke-width:2;fill:none}
    .s-users .stat-icon{background:#064e3b}
    .s-delivery .stat-icon{background:#1e3a8a}
    .s-active .stat-icon{background:#92400e}
    .s-revenue .stat-icon{background:#4c1d95}
    .qa-buttons{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px}
    .activity-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px}
    .activity-list li{background:#0b1220;border:1px solid var(--border);border-radius:10px;padding:10px}
    .activity-list .time{color:var(--muted);font-size:12px}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
    <div id="topbar"></div>
    <section class="stat-grid">
      <div class="card stat s-users">
        <div class="stat-row">
          <div class="stat-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <div class="stat-meta">
            <div class="stat-title">TOTAL CUSTOMERS</div>
            <div class="stat-value" id="statUsers">—</div>
          </div>
        </div>
      </div>
      <div class="card stat s-delivery">
        <div class="stat-row">
          <div class="stat-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 7h12v10H3z"/><path d="M15 11h4l3 3v3h-7z"/><circle cx="7.5" cy="19" r="2"/><circle cx="17.5" cy="19" r="2"/></svg>
          </div>
          <div class="stat-meta">
            <div class="stat-title">TOTAL DELIVERY</div>
            <div class="stat-value" id="statShipments">—</div>
          </div>
        </div>
      </div>
      <div class="card stat s-active">
        <div class="stat-row">
          <div class="stat-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 12h3l3 7 4-14 3 7h5"/></svg>
          </div>
          <div class="stat-meta">
            <div class="stat-title">ACTIVE BOOKINGS</div>
            <div class="stat-value" id="statActive">—</div>
          </div>
        </div>
      </div>
      <div class="card stat s-revenue">
        <div class="stat-row">
          <div class="stat-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div class="stat-meta">
            <div class="stat-title">REVENUE</div>
            <div class="stat-value" id="statRevenue">LKR 0.00</div>
          </div>
        </div>
      </div>
    </section>
    <section class="card">
      <h2>Quick Actions</h2>
      <div class="qa-buttons">
        <a class="btn" href="/APLX/frontend/admin/booking.php">Add New Booking</a>
        <a class="btn" href="/APLX/frontend/admin/shipments.php">Manage Shipments</a>
        <a class="btn" href="/APLX/frontend/admin/analytics.php">View Reports</a>
        <a class="btn" href="/APLX/frontend/admin/customers.php">Manage Customers</a>
        <a class="btn" href="/APLX/frontend/admin/services.php">Manage Services</a>
        <a class="btn" href="/APLX/frontend/admin/gallery.php">Manage Gallery</a>
        <a class="btn" href="/APLX/frontend/admin/contact.php">Edit Contact Details</a>
        <a class="btn" href="/APLX/frontend/admin/settings.php">Open Settings</a>
        <a class="btn" href="/APLX/frontend/admin/message_customer.php">Message Customer</a>
      </div>
    </section>
    <section class="card">
      <h2>Recent Activity</h2>
      <ul class="activity-list">
        <li>
          <div class="time">2025-09-25 13:37:01</div>
          <div class="desc"><strong>Booked</strong> — New shipment from ygydu to yufyud. AWB: PRCL23053368</div>
        </li>
        <li>
          <div class="time">2025-09-25 13:25:27</div>
          <div class="desc"><strong>Booked</strong> — New shipment from Aravinthan to Srithevi. AWB: PRCL58662683</div>
        </li>
        <li class="muted">For live data, open <a class="btn btn-sm btn-outline" href="/APLX/backend/admin/dashboard.php">Live Dashboard</a></li>
      </ul>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
<script>
// Dynamic admin stats
(function(){
  async function loadStats(){
    try{
      const res = await fetch('/APLX/backend/admin/stats_api.php', { cache:'no-store' });
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();
      if(!data || !data.ok) throw new Error('Bad payload');
      const n = (x)=> new Intl.NumberFormat('en-LK').format(Number(x||0));
      const c = (x)=> 'LKR ' + new Intl.NumberFormat('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(x||0));
      const eUsers = document.getElementById('statUsers');
      const eShip = document.getElementById('statShipments');
      const eAct  = document.getElementById('statActive');
      const eRev  = document.getElementById('statRevenue');
      if (eUsers) eUsers.textContent = n(data.total_users);
      if (eShip) eShip.textContent = n(data.total_shipments);
      if (eAct)  eAct.textContent  = n(data.active_bookings);
      if (eRev)  eRev.textContent  = c(data.revenue);
    }catch(err){
      console.error('Failed to load stats', err);
    }
  }
  loadStats();
  setInterval(loadStats, 60000);
})();
</script>
</body>
</html>
