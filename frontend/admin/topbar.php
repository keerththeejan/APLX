<style>
  .hamburger{background:transparent;border:2px solid #22c55e;color:#ffffff;padding:10px;border-radius:10px;box-shadow:none;font-size:26px;line-height:1;cursor:pointer}
  .hamburger:hover{filter:brightness(1.1)}
  .hamburger:focus{outline:none;box-shadow:0 0 0 2px rgba(34,197,94,.35)}
  .topbar{position:fixed;top:0;left:260px;right:16px;z-index:1000;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;padding:10px 12px;background:#0b1220!important;border:1px solid var(--border);border-radius:12px}
  body[data-theme="light"] .topbar{ background:#ffffff !important; }
  body.collapsed .topbar{ left:64px; }
  .content{ padding-top: 76px; }
  .icon-btn.notif{ position: relative; }
  .icon-btn.notif .notif-badge{position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;font-size:11px;line-height:1;padding:2px 6px;border-radius:999px;box-shadow:0 0 0 2px #0b1220;display:none}
  .icon-btn.notif .notif-badge.show{ display:inline-block }
  .modal-backdrop.open{ display:flex }
  .modal-panel{ width:100%; max-width:520px; background:#0b1220; border:1px solid var(--border); border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,.5) }
  body[data-theme="light"] .modal-panel{ background:#ffffff }
  .modal-header{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px 14px; border-bottom:1px solid var(--border) }
  .modal-title{ margin:0; font-size:18px }
  .modal-close{ background:#111827; color:#fff; border:1px solid var(--border); border-radius:8px; padding:6px 10px; cursor:pointer }
</style>
<div class="topbar">
  <div style="display:flex;align-items:center;gap:10px">
    <button class="hamburger" id="toggleSidebar" title="Toggle sidebar">≡</button>
    <h1 id="pageTitle">Dashboard</h1>
  </div>
  <div class="right" style="position:relative">
    <div class="clock" id="lk-clock">Loading...</div>
    <div class="icon-btn notif" id="notifBtn" title="Notifications">🔔<span class="notif-badge" id="notifBadge" aria-hidden="true">0</span></div>
    <div class="icon-btn" id="profileBtn" title="Admin Profile">👤</div>
    <div class="dropdown" id="notifMenu">
      <div class="item"><div class="title">No new notifications</div><div class="sub">You're up to date</div></div>
      <div class="item"><a class="btn btn-sm" id="notifViewAll" href="/APLX/frontend/admin/notifications.php">View All</a></div>
    </div>
    <div class="dropdown" id="profileMenu">
      <div class="item"><div class="title">Admin</div><div class="sub">admin@parcel.local</div></div>
      <div class="item"><a id="editProfileLink" class="btn btn-sm" href="/APLX/frontend/admin/profile.php">Edit Profile</a></div>
      <div class="item"><a class="btn btn-sm btn-outline" href="/APLX/backend/auth_logout.php">Logout</a></div>
    </div>
  </div>
</div>

<!-- Admin Profile Modal -->
<div id="adminProfileModal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-panel" style="max-width:520px">
    <div class="modal-header">
      <h3 class="modal-title">Admin Profile</h3>
      <button class="modal-close" id="adminProfileClose" type="button" aria-label="Close">✕</button>
    </div>
    <div class="modal-body">
      <form id="adminProfileForm" class="form-grid" method="post" action="/APLX/backend/admin/profile_update.php">
        <div class="form-row">
          <input type="text" name="name" placeholder="Full Name" required />
        </div>
        <div class="form-row two">
          <input type="email" name="email" placeholder="Email" required />
          <input type="tel" name="phone" placeholder="Phone (optional)" />
        </div>
        <div class="form-row two">
          <input type="password" name="password" placeholder="New Password (optional)" />
          <input type="password" name="confirm_password" placeholder="Confirm Password" />
        </div>
        <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end">
          <button type="button" class="btn btn-secondary" id="adminProfileCancel">Cancel</button>
          <button type="submit" class="btn">Save Changes</button>
        </div>
      </form>
      <div id="adminProfileStatus" class="inline-status" aria-live="polite"></div>
    </div>
  </div>
</div>

<!-- Notifications Modal -->
<div id="notifAllModal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-panel" style="max-width:720px">
    <div class="modal-header">
      <h3 class="modal-title">All Notifications</h3>
      <button class="modal-close" id="notifAllClose" type="button" aria-label="Close">✕</button>
    </div>
    <div class="modal-body">
      <ul id="notifList" class="activity-list" style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px">
        <li class="muted">Loading...</li>
      </ul>
    </div>
  </div>
</div>
