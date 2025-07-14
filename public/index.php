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

    'task' => fn($method, $id, $body) => match ($method) {
        'GET' => $id ? $taskController->show((int)$id) : $taskController->index(),

        'POST' => $taskController->store($body),

        'PUT' => match (true) {
            isset($_GET['id'], $_GET['status']) => $taskController->updateStatus((int)$_GET['id'], $_GET['status']),
            $id => $taskController->update((int)$id, $body),
            default => sendError("ID required", 400)
        },

        'DELETE' => $id ? $taskController->delete((int)$id) : sendError("ID required", 400),

        default => sendError("Method not allowed", 405)
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

    'message' => fn($method, $id, $body) => match ($method) {
        'GET' => match (true) {
            isset($_GET['id']) => $taskMessageController->show((int) $_GET['id']),
            isset($_GET['task_id']) => $taskMessageController->getByTaskId((int) $_GET['task_id']),
            default => $taskMessageController->index()
        },
        'POST' => $taskMessageController->store($body),
        'PUT' => isset($_GET['id']) ? $taskMessageController->update((int) $_GET['id'], $body) : sendError("ID required", 400),
        'DELETE' => isset($_GET['id']) ? $taskMessageController->delete((int) $_GET['id']) : sendError("ID required", 400),
        default => sendError("Method not allowed", 405)
    },

    'measurement' => fn($method, $id, $body) => match ($method) {
        'GET' => match (true) {
            isset($_GET['id']) => $measurementController->show((int) $_GET['id']),
            isset($_GET['task_id']) => $measurementController->getByTaskId((int) $_GET['task_id']),
            default => $measurementController->index()
        },
        'POST' => $measurementController->store($body),
        'PUT' => isset($_GET['id']) ? $measurementController->update((int) $_GET['id'], $body) : sendError("ID required", 400),
        'DELETE' => isset($_GET['id']) ? $measurementController->delete((int) $_GET['id']) : sendError("ID required", 400),
        default => sendError("Method not allowed", 405)
    },

    'service' => fn($method, $id, $body) => match ($method) {
        'GET' => match (true) {
            isset($_GET['id']) => $serviceController->show((int) $_GET['id']),
            isset($_GET['task_id']) => $serviceController->getByTaskId((int) $_GET['task_id']),
            default => $serviceController->index()
        },
        'POST' => $serviceController->store($body),
        'PUT' => isset($_GET['id']) ? $serviceController->update((int) $_GET['id'], $body) : sendError("ID required", 400),
        'DELETE' => isset($_GET['id']) ? $serviceController->delete((int) $_GET['id']) : sendError("ID required", 400),
        default => sendError("Method not allowed", 405)
    },

    'quote' => fn($method, $id, $body) => match ($method) {
        'GET' => match (true) {
            isset($_GET['id']) => $quoteController->show((int) $_GET['id']),
            isset($_GET['task_id']) => $quoteController->getByTaskId((int) $_GET['task_id']),
            default => $quoteController->index()
        },
        'POST'   => $quoteController->store($body),
        'PUT'    => $id ? $quoteController->update((int)$id, $body) : sendError("ID required", 400),
        'DELETE' => $id ? $quoteController->delete((int)$id) : sendError("ID required", 400),
        default  => sendError("Method not allowed", 405)
    },

    'service-master' => fn($method, $id, $body) => match ($method) {
        'GET'    => $id ? $serviceMasterController->show((int)$id) : $serviceMasterController->index(),
        'POST'   => $serviceMasterController->store($body),
        'PUT'    => $id ? $serviceMasterController->update((int)$id, $body) : sendError("ID required", 400),
        'DELETE' => $id ? $serviceMasterController->delete((int)$id) : sendError("ID required", 400),
        default  => sendError("Method not allowed", 405)
    },

    'quote-measurement' => fn($method, $id, $body) => match ($method) {
        'GET' => match (true) {
            isset($_GET['id']) => $quoteMeasurementController->show((int) $_GET['id']),
            isset($_GET['quote_id']) => $quoteMeasurementController->getAllByQuoteId((int) $_GET['quote_id']),
            default => $quoteMeasurementController->index()
        },
        'POST'   => $quoteMeasurementController->store($body),
        'PUT'    => $id ? $quoteMeasurementController->update((int)$id, $body) : sendError("ID required", 400),
        'DELETE' => $id ? $quoteMeasurementController->delete((int)$id) : sendError("ID required", 400),
        default  => sendError("Method not allowed", 405)
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
