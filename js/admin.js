document.addEventListener('DOMContentLoaded', () => {
  // While loading partials, hide layout to prevent flash/jumps
  document.body.setAttribute('data-admin-loading','1');
  // Safety: auto-clear loading flag in case of any unexpected error
  setTimeout(() => { document.body.removeAttribute('data-admin-loading'); }, 2000);
  // Also clear once window fully loads, as an extra guarantee
  window.addEventListener('load', () => {
    document.body.removeAttribute('data-admin-loading');
  });
  // Load partials (sequential to avoid PHP session lock on parallel requests)
  (async function loadPartials(){
    async function fetchWithTimeout(url, opts={}, ms=3500){
      const ctrl = new AbortController();
      const t = setTimeout(()=> ctrl.abort(), ms);
      try{
        const res = await fetch(url, { cache:'no-store', credentials:'same-origin', signal: ctrl.signal, ...opts });
        return res;
      } finally { clearTimeout(t); }
    }
    try{
      // Sidebar first
      const s = await fetchWithTimeout('/APLX/frontend/admin/sidebar.php');
      if (s.ok){
        const html = await s.text();
        const host = document.getElementById('sidebar');
        if (host) host.outerHTML = html;
      }
      // Then topbar
      const t = await fetchWithTimeout('/APLX/frontend/admin/topbar.php');
      if (t.ok){
        const html = await t.text();
        const host = document.getElementById('topbar');
        if (host) host.outerHTML = html;
      }
    } catch(_) { /* ignore */ }
    // Fallbacks: if partials missing, inject minimal markup
    try{
      if (!document.querySelector('.sidebar')){
        const sideHost = document.getElementById('sidebar');
        if (sideHost){
          sideHost.outerHTML = `
            <aside class="sidebar">
              <div>
                <div class="side-header"><div class="logo">📦</div><div class="app">Admin Panel</div></div>
                <nav>
                  <a href="/APLX/frontend/admin/dashboard.php"><span class="icon">🏠</span><span>Dashboard</span></a>
                  <a href="/APLX/frontend/admin/customers.php"><span class="icon">👥</span><span>Customers</span></a>
                  <a href="/APLX/frontend/admin/mail.php"><span class="icon">✉️</span><span>Mail</span></a>
                  <a href="/APLX/frontend/admin/shipments.php"><span class="icon">📦</span><span>Shipments</span></a>
                  <a href="/APLX/frontend/admin/booking.php"><span class="icon">📝</span><span>Bookings</span></a>
                  <a href="/APLX/frontend/admin/analytics.php"><span class="icon">📊</span><span>Analytics</span></a>
                  <a href="/APLX/frontend/admin/settings.php"><span class="icon">⚙️</span><span>Settings</span></a>
                </nav>
              </div>
            </aside>`;
        }
      }
      if (!document.querySelector('.topbar')){
        const topHost = document.getElementById('topbar');
        if (topHost){
          topHost.outerHTML = `
            <div class="topbar">
              <div style="display:flex;align-items:center;gap:10px">
                <button class="hamburger" id="toggleSidebar" title="Toggle sidebar">≡</button>
                <h1 id="pageTitle">Dashboard</h1>
                <div id="pinSlot" class="pin-slot" aria-live="polite"></div>
              </div>
              <div class="right" style="position:relative">
                <div class="clock" id="lk-clock">Loading...</div>
                <div class="icon-btn notif" id="notifBtn" title="Notifications">🔔<span class="notif-badge" id="notifBadge" aria-hidden="true">0</span></div>
                <div class="icon-btn" id="profileBtn" title="Admin Profile">👤</div>
              </div>
            </div>`;
        }
      }
    }catch(_){ }
  })().then(() => {
    // After both loaded, init behaviors
    initActiveAndTitle();
    initTopbarBehaviors();
    initSettingsPinBehavior();
    enablePjaxNav();
    enableNavPrefetch();
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
      if (/\/frontend\/admin\/(services|gallery|contact)\.(php|html)$/.test(curPath)) {
        const settings = Array.from(links).find(a => (a.getAttribute('href')||'').toLowerCase().endsWith('/frontend/admin/settings.php') || (a.getAttribute('href')||'').toLowerCase().endsWith('/frontend/admin/settings.html'));
        if (settings) { settings.classList.add('active'); activeSet = true; }
      }
    }catch(_){ }
    links.forEach(a => {
      try {
        const aUrl = new URL(a.href, window.location.origin);
        const aPath = aUrl.pathname.replace(/\/index\.(html|php)$/, '/');
        const cur = window.location.pathname.replace(/\/index\.(html|php)$/, '/');
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
        }catch(_){ }
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

  // Execute scripts present anywhere in the fetched document (head/body), skipping admin.js itself
  function executePageScripts(doc){
    try{
      const all = doc.querySelectorAll('script');
      all.forEach((s) => {
        const src = (s.getAttribute('src')||'').toLowerCase();
        if (src.includes('/aplx/js/admin.js')) return; // avoid re-injecting self
        const ns = document.createElement('script');
        for (const attr of s.attributes){ ns.setAttribute(attr.name, attr.value); }
        if (s.src){ ns.src = s.src; } else { ns.textContent = s.textContent || ''; }
        document.body.appendChild(ns);
        ns.parentNode && ns.parentNode.removeChild(ns);
      });
    }catch(_){ }
  }

  // Lightweight PJAX navigation for sidebar links
  function enablePjaxNav(){
    const nav = document.querySelector('.sidebar nav');
    if (!nav) return;
    nav.addEventListener('click', (e)=>{
      const a = e.target.closest('a');
      if (!a) return;
      const href = a.getAttribute('href');
      if (!href) return;
      // Same-origin only
      try{
        const url = new URL(href, window.location.origin);
        if (url.origin !== window.location.origin) return; // allow default
        e.preventDefault();
        pjaxNavigate(url.toString(), false);
      }catch(_){ /* ignore */ }
    });
    window.addEventListener('popstate', () => {
      pjaxNavigate(window.location.href, true);
    });
  }

  async function pjaxNavigate(href, replace){
    try{
      // Use prefetched response if present
      const cacheKey = new URL(href, window.location.origin).toString();
      let htmlText = prefetchCache.get(cacheKey);
      let res;
      if (!htmlText){
        res = await fetch(href, { cache:'no-store' });
        if (!res.ok) throw new Error('HTTP '+res.status);
        htmlText = await res.text();
      }
      const doc = new DOMParser().parseFromString(htmlText, 'text/html');
      const newMain = doc.querySelector('main.content');
      const curMain = document.querySelector('main.content');
      if (newMain && curMain){
        curMain.innerHTML = newMain.innerHTML;
      } else {
        // Missing expected content -> full load to ensure page works (e.g., login redirect or different layout)
        window.location.href = href; return;
      }
      const t = doc.querySelector('title');
      if (t) document.title = t.textContent || document.title;
      const url = new URL(href, window.location.origin);
      if (replace){ history.replaceState({url: url.pathname+url.search}, '', url.pathname+url.search); }
      else { history.pushState({url: url.pathname+url.search}, '', url.pathname+url.search); }
      // Reload topbar partial for the new page
      try{
        const top = await fetch('/APLX/frontend/admin/topbar.php', { cache:'no-store' });
        const topHtml = await top.text();
        const host = document.getElementById('topbar');
        if (host) host.outerHTML = topHtml;
      }catch(_){ }
      // Re-init behaviors for new content
      initActiveAndTitle();
      initTopbarBehaviors();
      initSettingsPinBehavior();
      // Execute inline scripts from the fetched main content
      if (newMain){ executeScriptsFrom(newMain); }
      // Also execute any page scripts present outside main (e.g., in head or after layout), excluding admin.js
      executePageScripts(doc);
    }catch(err){
      console.error('Navigation failed', err);
      window.location.href = href; // fallback to full load
    }finally{
      // Always clear potential loading indicators
      document.body.removeAttribute('data-admin-loading');
    }
  }

  function executeScriptsFrom(container){
    try{
      const scripts = container.querySelectorAll('script');
      scripts.forEach((s) => {
        const ns = document.createElement('script');
        // Copy attributes
        for (const attr of s.attributes){ ns.setAttribute(attr.name, attr.value); }
        if (s.src){
          ns.src = s.src;
        } else {
          ns.textContent = s.textContent || '';
        }
        document.body.appendChild(ns);
        // Remove to keep DOM clean
        ns.parentNode && ns.parentNode.removeChild(ns);
      });
    }catch(_){ /* ignore */ }
  }

  // Simple prefetch on hover for faster instant loads
  const prefetchCache = new Map(); // url -> html string
  function enableNavPrefetch(){
    const nav = document.querySelector('.sidebar nav');
    if (!nav) return;
    nav.addEventListener('mouseenter', attachPrefetch, { once: true });
    function attachPrefetch(){
      nav.querySelectorAll('a[href]').forEach(a => {
        a.addEventListener('mouseenter', () => prefetchLink(a), { passive: true });
        a.addEventListener('touchstart', () => prefetchLink(a), { passive: true });
      });
    }
  }
  async function prefetchLink(a){
    try{
      const href = a.getAttribute('href');
      if (!href) return;
      const url = new URL(href, window.location.origin).toString();
      if (prefetchCache.has(url)) return;
      const r = await fetch(url, { cache:'no-store' });
      if (!r.ok) return;
      const html = await r.text();
      prefetchCache.set(url, html);
      // expire after 30s to avoid staleness
      setTimeout(() => prefetchCache.delete(url), 30000);
    }catch(_){ }
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
    let latestNotifs = [];
    function getLastSeen(){ const v = localStorage.getItem('admin_notif_last_seen') || '0'; const n = parseInt(v,10); return isNaN(n)?0:n; }
    function itemMarker(it){ const id = Number(it.id||0); if (!isNaN(id) && id>0) return id; const t = Date.parse(it.created_at||it.time||''); return isNaN(t)?0:t; }
    function updateNotifBadge(items){ try{ const last = getLastSeen(); const unseen = (items||[]).filter(it => itemMarker(it) > last).length; if (notifBadge){ if (unseen>0){ notifBadge.textContent = String(unseen); notifBadge.style.display='inline-block'; } else { notifBadge.textContent=''; notifBadge.style.display='none'; } } }catch(_){ if (notifBadge){ notifBadge.textContent=''; notifBadge.style.display='none'; } } }
    function markNotificationsSeen(){ try{ const maxMark = (latestNotifs||[]).reduce((m,it)=>{ const k=itemMarker(it); return k>m?k:m; }, getLastSeen()); localStorage.setItem('admin_notif_last_seen', String(maxMark)); updateNotifBadge(latestNotifs); }catch(_){ } }
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
        // Best-effort sync of inbound mailbox before loading list with a short timeout
        try {
          const ctrl = new AbortController();
          const t = setTimeout(() => ctrl.abort(), 2500);
          await fetch('/APLX/backend/admin/mailbox_sync.php', { method:'POST', cache:'no-store', signal: ctrl.signal });
          clearTimeout(t);
        } catch(_){ /* ignore sync failures/timeouts */ }
        // Preferred API endpoint; adjust if your backend differs
        const res = await fetch('/APLX/backend/admin/notifications.php?api=1', { cache:'no-store' });
        if (!res.ok) throw new Error('HTTP '+res.status);
        const data = await res.json();
        const items = Array.isArray(data.items) ? data.items : [];
        latestNotifs = items;
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
        const id = String(n.id||'');
        const src = String(n.source||'');
        return `<li data-id="${id}" data-source="${src}" class="notif-item">
          <div class="desc"><strong>${title}</strong>${msg?` — ${msg}`:''}</div>
          <div class="time muted" style="font-size:12px">${time}</div>
        </li>`;
      }).join('');

      // Attach click to open detail for mailbox items
      notifList.querySelectorAll('.notif-item').forEach(li => {
        li.addEventListener('click', async () => {
          const id = li.getAttribute('data-id');
          const src = li.getAttribute('data-source');
          if (src !== 'admin_mailbox' || !id) return;
          try{
            const res = await fetch(`/APLX/backend/admin/mailbox_get.php?id=${encodeURIComponent(id)}`, { cache:'no-store' });
            if (!res.ok) throw new Error('HTTP '+res.status);
            const data = await res.json();
            if (data && data.ok && data.item){
              showMailboxDetail(data.item);
            }
          }catch(_){ /* ignore */ }
        });
      });
    }

    // Simple detail modal for full mailbox view
    const mbModal = document.createElement('div');
    mbModal.className = 'modal-backdrop';
    mbModal.setAttribute('aria-hidden','true');
    mbModal.innerHTML = `
      <div class="modal-panel" style="max-width:720px">
        <div class="modal-header">
          <h3 class="modal-title">Mail</h3>
          <button class="modal-close" id="mbDetailClose" type="button" aria-label="Close">✕</button>
        </div>
        <div class="modal-body">
          <div id="mbDetailMeta" class="muted" style="margin-bottom:8px"></div>
          <div id="mbDetailBody" style="white-space:pre-wrap"></div>
        </div>
      </div>`;
    document.body.appendChild(mbModal);
    function showMailboxDetail(item){
      try{
        const meta = mbModal.querySelector('#mbDetailMeta');
        const body = mbModal.querySelector('#mbDetailBody');
        const from = escapeHtml(item.from_email||'');
        const to = escapeHtml(item.to_email||'');
        const sub = escapeHtml(item.subject||'');
        const when = escapeHtml(item.created_at||'');
        meta.textContent = `${from} → ${to} • ${sub} • ${when}`;
        // show HTML as text fallback; if stored HTML, strip tags server-side in preview already
        body.textContent = String(item.body||'');
        mbModal.classList.add('open');
        mbModal.setAttribute('aria-hidden','false');
        document.body.style.overflow='hidden';
      }catch(_){ }
    }
    mbModal.addEventListener('click', (e)=>{ if(e.target===mbModal) closeMb(); });
    function closeMb(){ mbModal.classList.remove('open'); mbModal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
    document.addEventListener('click', closeAll);
    loadNotifications();
  }

  // Click a settings card to pin a compact pill near the header
  function initSettingsPinBehavior(){
    const grid = document.querySelector('.settings-grid');
    if (!grid) return;
    const pinSlot = document.getElementById('pinSlot');
    if (!pinSlot) return;

    // Delegate clicks on cards
    grid.addEventListener('click', (e) => {
      const card = e.target.closest('.setting-card');
      if (!card) return;
      const label = (card.querySelector('h3')?.textContent || 'Pinned').trim();
      const icon = (card.querySelector('.icon')?.textContent || '📌').trim();
      flyToPin(card, pinSlot, { label, icon });
    });

    function flyToPin(sourceEl, targetSlot, meta){
      try{
        const sRect = sourceEl.getBoundingClientRect();
        const tRect = targetSlot.getBoundingClientRect();
        // Create floating clone
        const ghost = document.createElement('div');
        ghost.style.position = 'fixed';
        ghost.style.left = sRect.left + 'px';
        ghost.style.top = sRect.top + 'px';
        ghost.style.width = sRect.width + 'px';
        ghost.style.height = sRect.height + 'px';
        ghost.style.borderRadius = '12px';
        ghost.style.background = getComputedStyle(sourceEl).backgroundColor || '#111827';
        ghost.style.border = '1px solid var(--border)';
        ghost.style.boxShadow = '0 20px 60px rgba(0,0,0,.35)';
        ghost.style.zIndex = '2000';
        ghost.style.display = 'flex';
        ghost.style.alignItems = 'center';
        ghost.style.justifyContent = 'center';
        ghost.style.color = getComputedStyle(sourceEl).color || '#fff';
        ghost.style.transition = 'transform .55s cubic-bezier(.2,.8,.2,1), opacity .55s ease, width .55s ease, height .55s ease';
        ghost.textContent = meta.label;
        document.body.appendChild(ghost);

        // Compute translate to target
        const toX = (tRect.left + Math.min(220, Math.max(0, tRect.width - 120)) ) - sRect.left;
        const toY = (tRect.top + 6) - sRect.top;

        // Shrink while moving
        requestAnimationFrame(() => {
          ghost.style.transform = `translate(${toX}px, ${toY}px) scale(0.3)`;
          ghost.style.opacity = '0.8';
          ghost.style.width = '140px';
          ghost.style.height = '40px';
          ghost.style.borderRadius = '999px';
        });

        const onDone = () => {
          ghost.removeEventListener('transitionend', onDone);
          ghost.remove();
          // Render/replace pinned pill
          targetSlot.innerHTML = '';
          const pill = document.createElement('div');
          pill.className = 'pinned-mini';
          pill.innerHTML = `<span class="icon">${meta.icon}</span><span class="label">${escapeHtml(meta.label)}</span>`;
          targetSlot.appendChild(pill);
        };
        ghost.addEventListener('transitionend', onDone);
      }catch(err){
        console.error(err);
      }
    }

    function escapeHtml(s){
      return String(s).replace(/[&<>"']/g, (c)=>({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'
      })[c]);
    }
  }
});


