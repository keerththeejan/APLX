<?php
// backend/admin/users.php (moved from users_api.php)
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        // Single item
        $stmt = $conn->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $item = $res->fetch_assoc();
        echo json_encode(['item' => $item]);
        exit;
    }
    // List with pagination and search
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(100, intval($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['search'] ?? '');
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = $conn->prepare('SELECT COUNT(*) c FROM users WHERE name LIKE ? OR email LIKE ?');
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
        $stmt = $conn->prepare('SELECT id, name, email, role, created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ssii', $like, $like, $limit, $offset);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $total = (int)($conn->query('SELECT COUNT(*) c FROM users')->fetch_assoc()['c'] ?? 0);
        $stmt = $conn->prepare('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    echo json_encode(['total' => (int)$total, 'items' => $items]);
    exit;
}

if ($method === 'POST') {
    // Create or update
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'customer');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Name and Email are required']);
        exit;
    }

    if ($id > 0) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET name=?, email=?, role=?, password_hash=? WHERE id=?');
            $stmt->bind_param('ssssi', $name, $email, $role, $hash, $id);
        } else {
            $stmt = $conn->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?');
            $stmt->bind_param('sssi', $name, $email, $role, $id);
        }
        $stmt->execute();
        echo json_encode(['ok' => true, 'id' => $id]);
        exit;
    } else {
        $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, role, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('ssss', $name, $email, $role, $hash);
        $stmt->execute();
        echo json_encode(['ok' => true, 'id' => $conn->insert_id]);
        exit;
    }
}

// Allow DELETE for removing a user by id
if ($method === 'DELETE') {
    // Support method override via _method when sent from forms or query
    $override = $_GET['_method'] ?? $_POST['_method'] ?? '';
    if ($override && strtoupper($override) !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid id']);
        exit;
    }
    // Do not allow deleting self (optional safety)
    $me = $_SESSION['user_id'] ?? 0;
    if ($me && $me == $id) {
        http_response_code(400);
        echo json_encode(['error' => 'Cannot delete current user']);
        exit;
    }
    $stmt = $conn->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
