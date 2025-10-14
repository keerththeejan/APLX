<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ShipmentModel.php';

class ShipmentsController extends Controller {
    private ShipmentModel $shipments;

    public function __construct(mysqli $db) {
        parent::__construct($db);
        $this->shipments = new ShipmentModel($db);
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
                    $item = $this->shipments->getById($id);
                    return $this->json(['item' => $item]);
                }
                $page = max(1, intval($_GET['page'] ?? 1));
                $limit = max(1, min(100, intval($_GET['limit'] ?? 10)));
                $all = isset($_GET['all']) && $_GET['all'] == '1';
                if ($all) { $limit = 1000; }
                $search = trim($_GET['search'] ?? '');
                $res = $this->shipments->list($page, $limit, $search, $all);
                return $this->json(['ok'=>true,'page'=>$page,'limit'=>$limit,'total'=>(int)$res['total'],'items'=>$res['items']]);

            case 'POST':
                $sender = trim($_POST['sender_name'] ?? '');
                $receiver = trim($_POST['receiver_name'] ?? '');
                $origin = trim($_POST['origin'] ?? '');
                $destination = trim($_POST['destination'] ?? '');
                $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0.0;
                $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : null;
                $status = trim($_POST['status'] ?? 'Booked');
                if (!$sender || !$receiver || !$origin || !$destination || $weight <= 0) {
                    return $this->json(['error'=>'Missing required fields'], 400);
                }
                $created = $this->shipments->create($sender, $receiver, $origin, $destination, $weight, $price, $status);
                return $this->json(['ok'=>true] + $created, 201);

            case 'PUT':
            case 'PATCH':
                $isJson = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
                $src = $isJson ? json_decode(file_get_contents('php://input'), true) ?: [] : $_POST;
                $id = intval($_GET['id'] ?? ($src['id'] ?? 0));
                if ($id <= 0) return $this->json(['error'=>'Invalid id'], 400);
                $ok = $this->shipments->update($id, $src);
                if (!$ok) return $this->json(['error'=>'Not found'], 404);
                return $this->json(['ok'=>true]);

            case 'DELETE':
                $id = intval($_GET['id'] ?? 0);
                if ($id <= 0) return $this->json(['error'=>'Invalid id'], 400);
                $ok = $this->shipments->delete($id);
                return $this->json(['ok'=>(bool)$ok]);

            default:
                return $this->json(['error' => 'Method not allowed'], 405);
        }
    }
}
