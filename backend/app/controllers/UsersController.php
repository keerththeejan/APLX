<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/UserModel.php';

class UsersController extends Controller {
    private UserModel $users;

    public function __construct(mysqli $db) {
        parent::__construct($db);
        $this->users = new UserModel($db);
        $this->requireAdmin();
    }

    public function handle(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $override = $_POST['_method'] ?? ($_GET['_method'] ?? null);
        if ($override) $method = strtoupper($override);

        if (in_array($method, ['POST','PUT','PATCH','DELETE'], true)) {
            $this->csrfForWrite();
        }

        switch ($method) {
            case 'GET':
                $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                if ($id > 0) {
                    $item = $this->users->getById($id);
                    return $this->json(['item' => $item]);
                }
                $page = max(1, intval($_GET['page'] ?? 1));
                $limit = max(1, min(100, intval($_GET['limit'] ?? 10)));
                $search = trim($_GET['search'] ?? '');
                $result = $this->users->list($page, $limit, $search);
                return $this->json(['total' => (int)$result['total'], 'items' => $result['items']]);
            case 'POST':
                $id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : 0;
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $role = trim($_POST['role'] ?? 'customer');
                $password = $_POST['password'] ?? null;
                if ($name === '' || $email === '') return $this->json(['error' => 'Name and Email are required'], 400);
                if ($id > 0) {
                    $ok = $this->users->update($id, $name, $email, $role, $password);
                    return $this->json(['ok' => (bool)$ok, 'id' => $id]);
                } else {
                    $newId = $this->users->create($name, $email, $role, $password);
                    return $this->json(['ok' => true, 'id' => $newId]);
                }
            case 'DELETE':
                $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                if ($id <= 0) return $this->json(['error' => 'Invalid id'], 400);
                $me = $_SESSION['user_id'] ?? 0;
                if ($me && $me == $id) return $this->json(['error' => 'Cannot delete current user'], 400);
                $ok = $this->users->delete($id);
                return $this->json(['ok' => (bool)$ok]);
            default:
                return $this->json(['error' => 'Method not allowed'], 405);
        }
    }
}
