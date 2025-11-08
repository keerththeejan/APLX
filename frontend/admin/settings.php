<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Settings</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .settings-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
    .setting-card{background:var(--panel-bg, #0b1220);border:1px solid var(--border);border-radius:12px;padding:16px;display:flex;flex-direction:column;gap:8px}
    .setting-card .icon{font-size:24px}
    .setting-card h3{margin:0}
    .setting-card p{margin:0;color:var(--muted)}
    .setting-card .actions{margin-top:8px;display:flex;gap:8px;flex-wrap:wrap}
  </style>
</head>
<body>
<div class="layout">
  <aside id="sidebar"></aside>
  <main class="content">
  <div id="topbar"></div>
  <section class="card">
    <h2>Settings</h2>
    <div class="settings-grid">
      <div class="setting-card">
        <div class="icon">🧩</div>
        <h3>Homepage Services</h3>
        <p>Manage the four service cards shown on the homepage (title, description, image/icon, order).</p>
        <div class="actions">
          <a class="btn" href="/APLX/frontend/admin/services.php">Open Services</a>
        </div>
      </div>
      <div class="setting-card">
        <div class="icon">📈</div>
        <h3>Home Stats</h3>
        <p>Edit image, title, subtext and three stat cards (numbers + labels) for the stats block.</p>
        <div class="actions">
          <a class="btn" href="/APLX/frontend/admin/home_stats.php">Open Home Stats</a>
        </div>
      </div>
      <div class="setting-card">
        <div class="icon">🖼️</div>
        <h3>Homepage Banner</h3>
        <p>Edit main hero title, subtitle, tagline, buttons and background image.</p>
        <div class="actions">
          <a class="btn" href="/APLX/frontend/admin/hero_banners.php">Open Banner</a>
        </div>
      </div>
      <div class="setting-card">
        <div class="icon">🖼️</div>
        <h3>Gallery (Slider)</h3>
        <p>Add, edit, reorder images for the auto-scrolling gallery with optional date badge and tag.</p>
        <div class="actions">
          <a class="btn" href="/APLX/frontend/admin/gallery.php">Open Gallery</a>
        </div>
      </div>
      <div class="setting-card">
        <div class="icon">🏷️</div>
        <h3>About Section</h3>
        <p>Manage the image, eyebrow, title, subtext and two feature bullets shown in the About block.</p>
        <div class="actions">
          <a class="btn" href="/APLX/frontend/admin/about.php">Open About</a>
        </div>
      </div>
      <div class="setting-card">
        <div class="icon">💡</div>
        <h3>Why Best</h3>
        <p>Edit the center image and four features for the "Why we are the best" section.</p>
        <div class="actions">
          <a class="btn" href="/APLX/frontend/admin/why_best.php">Open Why Best</a>
        </div>
      </div>
      <div class="setting-card">
        <div class="icon">📋</div>
        <h3>Services Tabs</h3>
        <p>Manage the left list and preview image bullets in the Services Tabs section on the homepage.</p>
        <div class="actions">
          <a class="btn" href="/APLX/frontend/admin/service_tabs.php">Open Service Tabs</a>
        </div>
      </div>
      <div class="setting-card">
        <div class="icon">🆘</div>
        <h3>Help + Quote (Left Panel)</h3>
        <p>Edit eyebrow, title, subtext, bullets and mini image/text shown on the left side of the Help + Quote section.</p>
        <div class="actions">
          <a class="btn" href="/APLX/frontend/admin/help_quote.php">Open Help + Quote</a>
        </div>
      </div>
      <div class="setting-card">
        <div class="icon">🗺️</div>
        <h3>Map / Find Us</h3>
        <p>Manage the header title, subtext, and Google Maps embed URL for the 'Find Us Here' section.</p>
        <div class="actions">
          <a class="btn" href="/APLX/frontend/admin/map_section.php">Open Map Settings</a>
        </div>
      </div>
      <div class="setting-card">
        <div class="icon">📞</div>
        <h3>Contact Details</h3>
        <p>Edit address, phone, email and opening hours used in the footer and contact info cards.</p>
        <div class="actions">
          <a class="btn" href="/APLX/frontend/admin/contact.php">Open Contact</a>
        </div>
      </div>
    </div>
  </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
</body>
</html>




