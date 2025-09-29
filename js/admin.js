document.addEventListener('DOMContentLoaded', () => {
  // Load partials
  Promise.all([
    fetch('/Parcel/frontend/admin/sidebar.html').then(r => r.text()).then(html => {
      const host = document.getElementById('sidebar');
      if (host) host.outerHTML = html;
    }),
    fetch('/Parcel/frontend/admin/topbar.html').then(r => r.text()).then(html => {
      const host = document.getElementById('topbar');
      if (host) host.outerHTML = html;
    })
  ]).then(() => {
    // After both loaded, init behaviors
    initActiveAndTitle();
    initTopbarBehaviors();
  }).catch(console.error);

  function initActiveAndTitle() {
    // Highlight active link in sidebar based on current URL
    const links = document.querySelectorAll('.sidebar nav a');
    let activeSet = false;
    links.forEach(a => {
      try {
        const aUrl = new URL(a.href, window.location.origin);
        const aPath = aUrl.pathname.replace(/\/index\.html$/, '/');
        const cur = window.location.pathname.replace(/\/index\.html$/, '/');
        if (cur === aPath) {
          a.classList.add('active');
          activeSet = true;
        }
      } catch (_) {}
    });
    // If not matched, fallback by title text
    const h = document.getElementById('pageTitle');
    if (h) {
      const act = document.querySelector('.sidebar nav a.active span:last-child') ||
                  document.querySelector('.sidebar nav a.active') ||
                  null;
      if (act && act.textContent.trim()) {
        h.textContent = act.textContent.trim();
      } else if (document.title) {
        h.textContent = document.title.replace('Admin | ', '').trim();
      }
    }
    // Hamburger toggle
    const btn = document.getElementById('toggleSidebar');
    btn && btn.addEventListener('click', () => {
      document.body.classList.toggle('collapsed');
    });
  }

  function initTopbarBehaviors() {
    // Live Sri Lanka time (Asia/Colombo)
    const clock = document.getElementById('lk-clock');
    if (clock) {
      const tz = 'Asia/Colombo';
      const tick = () => {
        const now = new Date();
        const date = new Intl.DateTimeFormat('en-GB', { timeZone: tz, year: 'numeric', month: 'long', day: '2-digit' }).format(now);
        const time = new Intl.DateTimeFormat('en-GB', { timeZone: tz, hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true }).format(now);
        const day = new Intl.DateTimeFormat('en-GB', { timeZone: tz, weekday: 'long' }).format(now);
        clock.innerHTML = `${date}<br>${time}<br>${day}`;
      };
      tick();
      setInterval(tick, 1000);
    }
    // Dropdowns
    const notifBtn = document.getElementById('notifBtn');
    const profileBtn = document.getElementById('profileBtn');
    const notifMenu = document.getElementById('notifMenu');
    const profileMenu = document.getElementById('profileMenu');
    function closeAll() { notifMenu?.classList.remove('open'); profileMenu?.classList.remove('open'); }
    notifBtn?.addEventListener('click', (e) => { e.stopPropagation(); const o = notifMenu.classList.toggle('open'); if (o) profileMenu.classList.remove('open'); });
    profileBtn?.addEventListener('click', (e) => { e.stopPropagation(); const o = profileMenu.classList.toggle('open'); if (o) notifMenu.classList.remove('open'); });
    document.addEventListener('click', closeAll);
  }
});
