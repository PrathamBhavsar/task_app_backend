<?php
header("Content-Type: application/json");

require_once 'errorHandler.php';
require_once 'helpers/response.php';
require_once 'routes/user.php';
require_once 'routes/taskPriority.php';
require_once 'routes/taskStatus.php';
require_once 'routes/client.php';
require_once 'routes/designer.php';
require_once 'routes/task.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$routes = [
    'user' => 'handleUserRoutes',
    'priority' => 'handleTaskPriorityRoutes',
    'status' => 'handleTaskStatusRoutes',
    'client' => 'handleClientRoutes',
    'designer' => 'handleDesignerRoutes',
    'task' => 'handleTaskRoutes',
];


$segments = explode('/', trim($requestUri, '/'));
$resource = $segments[1] ?? null;

if ($segments[0] !== 'api' || !isset($routes[$resource])) {
    sendError("Route not found", 404);
}

$handler = $routes[$resource];
$handler($requestMethod);
