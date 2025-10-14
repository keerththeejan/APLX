<?php
require_once __DIR__ . '/../../init.php';

abstract class Controller {
    protected mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
        header('Content-Type: application/json');
    }

    protected function json($data, int $code = 200): void {
        http_response_code($code);
        echo json_encode($data);
        exit;
    }

    protected function requireAdmin(): void {
        require_admin();
    }

    protected function csrfForWrite(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array(strtoupper($method), ['POST','PUT','PATCH','DELETE'], true)) {
            csrf_check();
        }
    }
}
