<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../errorHandler.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../routes/user.php';
require_once __DIR__ . '/../routes/client.php';
require_once __DIR__ . '/../routes/designer.php';
require_once __DIR__ . '/../routes/task.php';
require_once __DIR__ . '/../routes/measurement.php';
require_once __DIR__ . '/../routes/service.php';
require_once __DIR__ . '/../routes/bill.php';
require_once __DIR__ . '/../routes/timeline.php';
require_once __DIR__ . '/../routes/message.php';
require_once __DIR__ . '/../routes/serviceMaster.php';
require_once __DIR__ . '/../routes/quote.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$routes = [
    'user' => 'handleUserRoutes',
    'client' => 'handleClientRoutes',
    'designer' => 'handleDesignerRoutes',
    'task' => 'handleTaskRoutes',
    'measurement' => 'handleMeasurementRoutes',
    'service' => 'handleServiceRoutes',
    'bill' => 'handleBillRoutes',
    'timeline' => 'handleTimelineRoutes',
    'message' => 'handleMessageRoutes',
    'service-master' => 'handleServiceMasterRoutes',
    'quote' => 'handleQuoteRoutes',
];


$segments = explode('/', trim($requestUri, '/'));
$resource = $segments[1] ?? null;

if ($segments[0] !== 'api' || !isset($routes[$resource])) {
    sendError("Route not found", 404);
}

$handler = $routes[$resource];
$handler($requestMethod);
