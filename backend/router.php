<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/app/controllers/UsersController.php';
require_once __DIR__ . '/app/controllers/ShipmentsController.php';

header('Content-Type: application/json');

$route = trim($_GET['route'] ?? '', '/');
// Guard: any route under admin/* requires an authenticated admin session
if (strpos($route, 'admin/') === 0) {
    require_admin();
}

try {
    switch ($route) {
        case 'admin/users':
            (new UsersController($conn))->handle();
            break;
        case 'admin/shipments':
            (new ShipmentsController($conn))->handle();
            break;
        // Delegate routes to existing JSON endpoints for now
        case 'admin/services':
            require __DIR__ . '/admin/services_api.php';
            break;
        case 'admin/gallery':
            require __DIR__ . '/admin/gallery_api.php';
            break;
        case 'admin/hero':
            require __DIR__ . '/admin/hero_api.php';
            break;
        case 'admin/customers':
            require __DIR__ . '/admin/customers_api.php';
            break;
        case 'admin/stats':
            require __DIR__ . '/admin/stats_api.php';
            break;
        case 'admin/site-contact':
            require __DIR__ . '/admin/site_contact_api.php';
            break;
        case 'admin/profile/get':
            require __DIR__ . '/admin/profile_get.php';
            break;
        case 'admin/profile/update':
            require __DIR__ . '/admin/profile_update.php';
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Route not found', 'route' => $route]);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
