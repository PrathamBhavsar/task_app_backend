<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../errorHandler.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../routes/user.php';
require_once __DIR__ . '/../routes/taskPriority.php';
require_once __DIR__ . '/../routes/taskStatus.php';
require_once __DIR__ . '/../routes/client.php';
require_once __DIR__ . '/../routes/designer.php';
require_once __DIR__ . '/../routes/task.php';
require_once __DIR__ . '/../routes/measurement.php';
require_once __DIR__ . '/../routes/service.php';
require_once __DIR__ . '/../routes/bill.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$routes = [
    'user' => 'handleUserRoutes',
    'priority' => 'handleTaskPriorityRoutes',
    'status' => 'handleTaskStatusRoutes',
    'client' => 'handleClientRoutes',
    'designer' => 'handleDesignerRoutes',
    'task' => 'handleTaskRoutes',
    'measurement' => 'handleMeasurementRoutes',
    'service' => 'handleServiceRoutes',
    'bill' => 'handleBillRoutes',
];


$segments = explode('/', trim($requestUri, '/'));
$resource = $segments[1] ?? null;

if ($segments[0] !== 'api' || !isset($routes[$resource])) {
    sendError("Route not found", 404);
}

$handler = $routes[$resource];
$handler($requestMethod);
