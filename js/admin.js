document.addEventListener('DOMContentLoaded', () => {
  // While loading partials, hide layout to prevent flash/jumps
  document.body.setAttribute('data-admin-loading','1');
  // Load partials
  Promise.all([
    fetch('/APLX/frontend/admin/sidebar.php', { cache:'no-store' }).then(r => r.text()).then(html => {
      const host = document.getElementById('sidebar');
      if (host) host.outerHTML = html;
    }),
    fetch('/APLX/frontend/admin/topbar.php', { cache:'no-store' }).then(r => r.text()).then(html => {
      const host = document.getElementById('topbar');
      if (host) host.outerHTML = html;
    })
  ]).then(() => {
    // After both loaded, init behaviors
    initActiveAndTitle();
    initTopbarBehaviors();
    // Reveal layout
    document.body.removeAttribute('data-admin-loading');
  }).catch(console.error);

  function initActiveAndTitle() {
    // Highlight active link in sidebar based on current URL
    const links = document.querySelectorAll('.sidebar nav a');
    // Clear any pre-set actives from the partial markup
    links.forEach(a => a.classList.remove('active'));
    let activeSet = false;
    // Treat specific pages as Settings
    try{
      const curPath = (window.location.pathname || '').toLowerCase();
      if (/\/frontend\/admin\/(services|gallery|contact)\.html$/.test(curPath)) {
        const settings = Array.from(links).find(a => (a.getAttribute('href')||'').toLowerCase().endsWith('/frontend/admin/settings.html'));
        if (settings) { settings.classList.add('active'); activeSet = true; }
      }
    }catch(_){ }
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
    // Fallback: filename based match (handles differing base prefixes like /APLX)
    if (!activeSet) {
      const curFile = (window.location.pathname.split('/').pop() || '').toLowerCase();
      links.forEach(a => {
        if (activeSet) return;
        try{
          const aFile = (new URL(a.href, window.location.origin).pathname.split('/').pop() || '').toLowerCase();
          if (aFile && aFile === curFile) { a.classList.add('active'); activeSet = true; }
        }catch(_){}
      });
    }
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
    const notifBadge = document.getElementById('notifBadge');
    function closeAll() { notifMenu?.classList.remove('open'); profileMenu?.classList.remove('open'); }
    notifBtn?.addEventListener('click', (e) => {
      e.stopPropagation();
      const o = notifMenu.classList.toggle('open');
      if (o) {
        profileMenu?.classList.remove('open');
        markNotificationsSeen();
      }
    });
    // Profile icon -> open modal form
    const profileModal = document.getElementById('adminProfileModal');
    const profileClose = document.getElementById('adminProfileClose');
    const profileCancel = document.getElementById('adminProfileCancel');
    const profileForm = document.getElementById('adminProfileForm');
    const profileStatus = document.getElementById('adminProfileStatus');
    const editProfileLink = document.getElementById('editProfileLink');
    async function openProfile(){
      closeAll();
      if (!profileModal) return;
      profileModal.classList.add('open');
      profileModal.setAttribute('aria-hidden','false');
      document.body.style.overflow='hidden';
      // Prefill form
      try{
        if (profileForm){
          profileStatus && (profileStatus.textContent = '');
          const res = await fetch('/APLX/backend/admin/profile_get.php', { cache:'no-store' });
          if (res.ok){
            const data = await res.json();
            const it = data.item || {};
            const set = (name, val)=>{ const el = profileForm.querySelector(`[name="${name}"]`); if (el) el.value = val||''; };
            set('name', it.name);
            set('email', it.email);
            set('phone', it.phone);
            set('company', it.company);
            set('address', it.address);
            set('city', it.city);
            set('state', it.state);
            set('country', it.country);
            set('pincode', it.pincode);
          }
        }
      }catch(e){ /* silent */ }
    }
    function closeProfile(){
      if (!profileModal) return;
      profileModal.classList.remove('open');
      profileModal.setAttribute('aria-hidden','true');
      document.body.style.overflow='';
      profileStatus && (profileStatus.textContent = '');
    }
    profileBtn?.addEventListener('click', (e) => { e.stopPropagation(); openProfile(); });
    editProfileLink?.addEventListener('click', (e)=>{ e.preventDefault(); e.stopPropagation(); openProfile(); });
    profileClose?.addEventListener('click', closeProfile);
    profileCancel?.addEventListener('click', closeProfile);
    profileModal?.addEventListener('click', (e) => { if (e.target === profileModal) closeProfile(); });
    window.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') closeProfile(); });
    // Optional: AJAX submit placeholder (stays on page)
    profileForm?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      profileStatus.textContent = 'Saving...';
      try{
        const fd = new FormData(profileForm);
        const res = await fetch(profileForm.action, { method:'POST', body: fd });
        if (!res.ok) throw new Error('Request failed');
        profileStatus.textContent = 'Profile updated successfully';
      } catch(err){
        profileStatus.textContent = 'Failed to update profile';
      }
    });
    // Notifications: View All -> open modal and fetch list
    const notifViewAll = document.getElementById('notifViewAll');
    const notifAllModal = document.getElementById('notifAllModal');
    const notifAllClose = document.getElementById('notifAllClose');
    const notifList = document.getElementById('notifList');

    function openNotifModal(){
      if (!notifAllModal) return;
      closeAll();
      notifAllModal.classList.add('open');
      notifAllModal.setAttribute('aria-hidden','false');
      document.body.style.overflow='hidden';
      if (notifList) notifList.innerHTML = '<li class="muted">Loading...</li>';
      loadNotifications().then(()=> markNotificationsSeen());
    }
    function closeNotifModal(){
      if (!notifAllModal) return;
      notifAllModal.classList.remove('open');
      notifAllModal.setAttribute('aria-hidden','true');
      document.body.style.overflow='';
    }
    notifViewAll?.addEventListener('click', (e)=>{ e.stopPropagation(); openNotifModal(); });
    notifAllClose?.addEventListener('click', closeNotifModal);
    notifAllModal?.addEventListener('click', (e)=>{ if (e.target === notifAllModal) closeNotifModal(); });
    window.addEventListener('keydown', (e)=>{ if (e.key==='Escape') closeNotifModal(); });

    async function loadNotifications(){
      try{
        // Preferred API endpoint; adjust if your backend differs
        const res = await fetch('/APLX/backend/admin/notifications.php?api=1', { cache:'no-store' });
        if (!res.ok) throw new Error('HTTP '+res.status);
        const data = await res.json();
        const items = Array.isArray(data.items) ? data.items : [];
        renderNotifications(items);
        updateNotifBadge(items);
      }catch(err){
        if (notifList) notifList.innerHTML = '<li class="muted">Failed to load notifications</li>';
      }
    }

    function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m])); }
    function renderNotifications(items){
      if (!notifList) return;
      if (!items.length){ notifList.innerHTML = '<li class="muted">No notifications</li>'; return; }
      notifList.innerHTML = items.map((n)=>{
        const title = escapeHtml(n.title || n.type || 'Notification');
        const msg = escapeHtml(n.message || n.body || '');
        const time = escapeHtml(n.created_at || n.time || '');
        return `<li>
          <div class="desc"><strong>${title}</strong>${msg?` — ${msg}`:''}</div>
          <div class="time muted" style="font-size:12px">${time}</div>
        </li>`;
      }).join('');
    }

    document.addEventListener('click', closeAll);
    // Initial badge/load
    loadNotifications();
  }
});


