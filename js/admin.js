document.addEventListener('DOMContentLoaded', () => {
  // Load partials
  Promise.all([
    fetch('/APLX/Parcel/frontend/admin/sidebar.html').then(r => r.text()).then(html => {
      const host = document.getElementById('sidebar');
      if (host) host.outerHTML = html;
    }),
    fetch('/APLX/Parcel/frontend/admin/topbar.html').then(r => r.text()).then(html => {
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
    // Clear any pre-set actives from the partial markup
    links.forEach(a => a.classList.remove('active'));
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
    function closeAll() { notifMenu?.classList.remove('open'); profileMenu?.classList.remove('open'); }
    notifBtn?.addEventListener('click', (e) => { e.stopPropagation(); const o = notifMenu.classList.toggle('open'); if (o) profileMenu.classList.remove('open'); });
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
          const res = await fetch('/APLX/Parcel/backend/admin/profile_get.php', { cache:'no-store' });
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
    document.addEventListener('click', closeAll);
  }
});
