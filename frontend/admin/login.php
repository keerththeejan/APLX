<?php
require_once __DIR__ . '/../../backend/config.php';
// If already logged in as admin, go to dashboard
if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin') {
    header('Location: /APLX/frontend/admin/dashboard.php');
    exit;
}
$next = isset($_GET['next']) ? (string)$_GET['next'] : '/APLX/frontend/admin/dashboard.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    body.login-page{background:#0b1220;color:#e2f3f6;min-height:100vh}
    .login-stage{min-height:100vh;display:grid;place-items:center;padding:16px}
    .card{max-width:520px;margin:auto;background:#0b3640;border:1px solid #0f2d35;color:#e2f3f6;border-radius:14px;box-shadow:0 12px 28px rgba(0,0,0,.35)}
    .card .inner{padding:20px}
    .input{padding:12px 14px;border-radius:10px;background:#09303a;border:1px solid #0f2d35;color:#e2f3f6;width:100%}
    .row{display:grid;gap:10px;margin-top:10px}
    .btn{padding:10px 14px;border-radius:10px;border:1px solid #0f2d35;background:#14532d;color:#e2f3f6;cursor:pointer;width:fit-content}
    .muted{color:#cde7ec}
    .header{display:flex;align-items:center;justify-content:space-between}
    .header h2{margin:0}
    .login-close{background:#0f172a;border:1px solid #0f2d35;color:#fff;border-radius:10px;padding:6px 10px;cursor:pointer}
    .err{margin:8px 0 0;color:#fca5a5;font-weight:600;display:none}
  </style>
</head>
<body class="login-page">
  <main class="login-stage">
    <section class="card">
      <div class="inner">
        <div class="header">
          <h2>Admin Login</h2>
          <button type="button" class="login-close" onclick="window.location.href='/APLX/frontend/index.php'">✕</button>
        </div>
        <p class="muted">Sign in with your administrator account.</p>
        <p id="loginError" class="err">Invalid email or password.</p>
        <form id="adminLoginForm" class="row" method="post" action="/APLX/backend/auth_login.php">
          <input type="hidden" name="role" value="admin">
          <input type="hidden" name="next" value="<?php echo htmlspecialchars($next, ENT_QUOTES, 'UTF-8'); ?>">
          <input class="input" name="email" type="email" placeholder="Email" required>
          <input class="input" id="pass" name="password" type="password" placeholder="Password" required>
          <button class="btn" type="submit">Login</button>
        </form>
      </div>
    </section>
  </main>
  <script>
    (function(){
      const params = new URLSearchParams(location.search);
      if (params.get('status') === 'error') {
        const el = document.getElementById('loginError');
        if (el) el.style.display = 'block';
      }
    })();
  </script>
</body>
</html>
