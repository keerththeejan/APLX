<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Analytics</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .grid-charts{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px}
    .card h3{margin-bottom:8px}
    canvas{max-width:100%;height:280px}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
  <div id="topbar"></div>
  <section class="card">
    <h2>Analytics</h2>
    <div class="grid-charts">
      <div class="card">
        <h3>Weekly Shipments (Bar)</h3>
        <canvas id="chartWeekly"></canvas>
      </div>
      <div class="card">
        <h3>Weekly Status (Pie)</h3>
        <canvas id="chartStatus"></canvas>
      </div>
      <div class="card">
        <h3>Yearly Shipments (Line)</h3>
        <canvas id="chartYear"></canvas>
      </div>
    </div>
    <div class="grid cards" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;margin-top:14px">
      <div class="card">
        <h3>Top Routes</h3>
        <ul id="anlRoutes" class="activity-list" style="margin-top:8px"></ul>
      </div>
      <div class="card">
        <h3>Delivery SLA</h3>
        <div id="anlSla" style="margin-top:8px" class="muted">Loading...</div>
      </div>
    </div>
  </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  function fmt(n){ return new Intl.NumberFormat('en-LK').format(Number(n||0)); }
  function li(text, sub){ return `<li><div class="desc"><strong>${text}</strong>${sub?` — <span class='muted'>${sub}</span>`:''}</div></li>`; }

  function makeWeeklyBar(ctx, weekly){
    const labels = weekly.map(r=> r.d);
    const data = weekly.map(r=> Number(r.c||0));
    return new Chart(ctx, { type:'bar', data:{ labels, datasets:[{ label:'Shipments', data, backgroundColor:'#22c55e' }] }, options:{ plugins:{legend:{display:false}}, scales:{ x:{ticks:{color:'#9ca3af'}}, y:{ticks:{color:'#9ca3af'}, beginAtZero:true } } } });
  }

  function makeStatusPie(ctx, byStatus){
    const labels = Object.keys(byStatus||{});
    const data = labels.map(k=> Number(byStatus[k]||0));
    const colors = ['#22c55e','#3b82f6','#eab308','#ef4444','#a78bfa','#f97316'];
    return new Chart(ctx, { type:'pie', data:{ labels, datasets:[{ data, backgroundColor: labels.map((_,i)=> colors[i%colors.length]) }] }, options:{ plugins:{ legend:{ labels:{ color:'#e5e7eb' }}}}});
  }

  function makeYearLine(ctx, series){
    const labels = series.map(r=> r.ym);
    const data = series.map(r=> Number(r.c||0));
    return new Chart(ctx, { type:'line', data:{ labels, datasets:[{ label:'Shipments', data, borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.2)', tension:.3, fill:true }] }, options:{ plugins:{legend:{display:false}}, scales:{ x:{ticks:{color:'#9ca3af'}}, y:{ticks:{color:'#9ca3af'}, beginAtZero:true } } } });
  }

  async function load(){
    try{
      const res = await fetch('/APLX/backend/admin/analytics.php?api=1', { cache:'no-store' });
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();

      const weekly = Array.isArray(data.by_day)? data.by_day: [];
      const byStatus = data.by_status || {};
      const byYear = Array.isArray(data.year_by_month)? data.year_by_month: [];

      const ctxW = document.getElementById('chartWeekly');
      const ctxS = document.getElementById('chartStatus');
      const ctxY = document.getElementById('chartYear');
      if (ctxW && weekly.length) makeWeeklyBar(ctxW, weekly);
      if (ctxS && Object.keys(byStatus).length) makeStatusPie(ctxS, byStatus);
      if (ctxY && byYear.length) makeYearLine(ctxY, byYear);

      // Routes list
      const routes = Array.isArray(data.top_routes)? data.top_routes: [];
      const routesEl = document.getElementById('anlRoutes');
      if (routesEl){
        routesEl.innerHTML = routes.length
          ? routes.map(r=> li(r.route, fmt(r.count))).join('')
          : '<li class="muted">No data</li>';
      }
      // SLA KPIs
      const slaEl = document.getElementById('anlSla');
      if (slaEl){
        const sla = data.sla||{};
        const avg = Number(sla.avg_hours||0).toFixed(1);
        const pct = Number(sla.pct_within_72h||0).toFixed(1);
        slaEl.classList.remove('muted');
        slaEl.innerHTML = `Delivered: <strong>${fmt(sla.delivered_count||0)}</strong><br>Average Hours: <strong>${avg}h</strong><br>Within 72h: <strong>${pct}%</strong>`;
      }
    }catch(err){
      console.error('Analytics load failed', err);
      const s=document.getElementById('anlSla'); if(s) s.textContent='Failed to load';
    }
  }
  load();
})();
</script>
</body>
</html>


