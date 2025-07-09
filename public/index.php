<?php

declare(strict_types=1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// Autoload and helpers
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../errorHandler.php';
require_once __DIR__ . '/../config/controllers.php';

// Route Parsing
$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($requestUri, '/'));
$resource = $segments[1] ?? null;
$id = $_GET['id'] ?? null;
$body = json_decode(file_get_contents("php://input"), true);

// Routes
$routes = [

    'designer' => fn($method, $id, $body) => match ($method) {
        'GET'    => $id ? $designerController->show((int)$id) : $designerController->index(),
        'POST'   => $designerController->store($body),
        'PUT'    => $id ? $designerController->update((int)$id, $body) : sendError("ID required", 400),
        'DELETE' => $id ? $designerController->delete((int)$id) : sendError("ID required", 400),
        default  => sendError("Method not allowed", 405)
    },

    'client' => fn($method, $id, $body) => match ($method) {
        'GET'    => $id ? $clientController->show((int)$id) : $clientController->index(),
        'POST'   => $clientController->store($body),
        'PUT'    => $id ? $clientController->update((int)$id, $body) : sendError("ID required", 400),
        'DELETE' => $id ? $clientController->delete((int)$id) : sendError("ID required", 400),
        default  => sendError("Method not allowed", 405)
    },

    'user' => fn($method, $id, $body) => match ($method) {
        'GET'    => $id ? $userController->show((int)$id) : $userController->index(),
        'POST'   => $userController->store($body),
        'PUT'    => $id ? $userController->update((int)$id, $body) : sendError("ID required", 400),
        'DELETE' => $id ? $userController->delete((int)$id) : sendError("ID required", 400),
        default  => sendError("Method not allowed", 405)
    },

    'timeline' => fn($method, $id, $body) => match ($method) {
        'GET' => match (true) {
            isset($_GET['id']) => $timelineController->show((int) $_GET['id']),
            isset($_GET['task_id']) => $timelineController->getByTaskId((int) $_GET['task_id']),
            default => $timelineController->index()
        },
        'POST' => $timelineController->store($body),
        'PUT' => isset($_GET['id']) ? $timelineController->update((int) $_GET['id'], $body) : sendError("ID required", 400),
        'DELETE' => isset($_GET['id']) ? $timelineController->delete((int) $_GET['id']) : sendError("ID required", 400),
        default => sendError("Method not allowed", 405)
    },

    'auth' => fn($method, $id, $body) => match ($method) {
        'POST' => match ($segments[2] ?? null) {
            'login' => $authController->login($body),
            'register' => $authController->register($body),
            default => sendError("Missing or invalid action", 400)
        },
        default => sendError("Method not allowed", 405)
    }

];

// Route Guard
if ($segments[0] !== 'api' || !isset($routes[$resource])) {
    sendError("Route not found", 404);
}

$routes[$resource]($requestMethod, $id, $body);
