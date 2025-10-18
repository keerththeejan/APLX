<?php
// backend/migrate.php
// Run from browser: /APLX/backend/migrate.php (requires admin session)
// Or from CLI: php backend/migrate.php
require_once __DIR__ . '/init.php';

function respond($data, $code = 200){
  if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
    http_response_code($code);
  }
  echo json_encode($data, JSON_PRETTY_PRINT);
  if (php_sapi_name() !== 'cli') exit; else return;
}

if (php_sapi_name() !== 'cli') {
  // Web guard
  require_admin();
}

// Ensure migrations table
$conn->query("CREATE TABLE IF NOT EXISTS migrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$applied = [];
$res = $conn->query('SELECT name FROM migrations ORDER BY id');
while ($row = $res->fetch_assoc()) { $applied[$row['name']] = true; }

$dir = __DIR__ . '/migrations';
if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
$files = glob($dir . '/*.php');
sort($files, SORT_NATURAL);

$ran = [];
$errors = [];
foreach ($files as $file) {
  $name = basename($file);
  if (isset($applied[$name])) continue;
  $fn = require $file;
  if (!is_callable($fn)) { $errors[] = [ 'name'=>$name, 'error'=>'Migration did not return a callable' ]; continue; }
  $conn->begin_transaction();
  try {
    $fn($conn);
    $stmt = $conn->prepare('INSERT INTO migrations(name) VALUES (?)');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $conn->commit();
    $ran[] = $name;
  } catch (Throwable $e) {
    $conn->rollback();
    $errors[] = [ 'name'=>$name, 'error'=>$e->getMessage() ];
  }
}

respond([ 'ok'=> empty($errors), 'applied_now'=>$ran, 'errors'=>$errors, 'total_migrations'=>count($files) ]);
