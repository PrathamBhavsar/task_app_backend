<?php

declare(strict_types=1);

// Set JSON and CORS headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// Composer Autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Helpers
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../errorHandler.php';

// Import namespaces (adjust paths if needed)
use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\DesignerRepository;
use Interface\Controller\DesignerController;
use Application\UseCase\{
    GetAllDesignersUseCase,
    GetDesignerByIdUseCase,
    CreateDesignerUseCase,
    UpdateDesignerUseCase,
    DeleteDesignerUseCase
};

// Setup database + repository + controller
$em = EntityManagerFactory::create();
$designerRepo = new DesignerRepository($em);

$designerController = new DesignerController(
    new GetAllDesignersUseCase($designerRepo),
    new GetDesignerByIdUseCase($designerRepo),
    new CreateDesignerUseCase($designerRepo),
    new UpdateDesignerUseCase($designerRepo),
    new DeleteDesignerUseCase($designerRepo),
);

// Parse URI
$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($requestUri, '/'));

$resource = $segments[1] ?? null;
$id = $_GET['id'] ?? null;
$body = json_decode(file_get_contents("php://input"), true);

// Route map — only Designer for now
$routes = [
    'designer' => fn($method, $id, $body) => match ($method) {
        'GET'    => $id ? $designerController->show((int)$id) : $designerController->index(),
        'POST'   => $designerController->store($body),
        'PUT'    => $id ? $designerController->update((int)$id, $body) : sendError("ID required", 400),
        'DELETE' => $id ? $designerController->delete((int)$id) : sendError("ID required", 400),
        default  => sendError("Method not allowed", 405)
    }
];

// Ensure the route is valid and within /api/*
if ($segments[0] !== 'api' || !isset($routes[$resource])) {
    sendError("Route not found", 404);
}

$routes[$resource]($requestMethod, $id, $body);
