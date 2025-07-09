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
use Application\UseCase\Designer\{
    GetAllDesignersUseCase,
    GetDesignerByIdUseCase,
    CreateDesignerUseCase,
    UpdateDesignerUseCase,
    DeleteDesignerUseCase
};

use Infrastructure\Persistence\Doctrine\ClientRepository;
use Interface\Controller\ClientController;
use Application\UseCase\Client\{
    GetAllClientsUseCase,
    GetClientByIdUseCase,
    CreateClientUseCase,
    UpdateClientUseCase,
    DeleteClientUseCase
};

use Infrastructure\Persistence\Doctrine\UserRepository;
use Interface\Controller\UserController;
use Application\UseCase\User\{
    GetAllUsersUseCase,
    GetUserByIdUseCase,
    CreateUserUseCase,
    UpdateUserUseCase,
    DeleteUserUseCase
};

use Infrastructure\Persistence\Doctrine\TimelineRepository;
use Interface\Controller\TimelineController;
use Application\UseCase\Timeline\{
    GetAllTimelinesUseCase,
    GetAllTimelinesByTaskIdUseCase,
    GetTimelineByIdUseCase,
    CreateTimelineUseCase,
    UpdateTimelineUseCase,
    DeleteTimelineUseCase
};

use Infrastructure\Persistence\Doctrine\AuthRepository;
use Interface\Controller\AuthController;
use Application\UseCase\Auth\{
    LoginUseCase,
    RegisterUseCase
};

// Setup database + repository + controller
$em = EntityManagerFactory::create();
$designerRepo = new DesignerRepository($em);
$clientRepo = new ClientRepository($em);
$userRepo = new UserRepository($em);
$timelineRepo = new TimeLineRepository($em);
$authRepo = new AuthRepository($em);

$designerController = new DesignerController(
    new GetAllDesignersUseCase($designerRepo),
    new GetDesignerByIdUseCase($designerRepo),
    new CreateDesignerUseCase($designerRepo),
    new UpdateDesignerUseCase($designerRepo),
    new DeleteDesignerUseCase($designerRepo),
);

$clientController = new ClientController(
    new GetAllClientsUseCase($clientRepo),
    new GetClientByIdUseCase($clientRepo),
    new CreateClientUseCase($clientRepo),
    new UpdateClientUseCase($clientRepo),
    new DeleteClientUseCase($clientRepo),
);

$timelineController = new TimelineController(
    new GetAllTimelinesUseCase($timelineRepo),
    new GetAllTimelinesByTaskIdUseCase($timelineRepo),
    new GetTimelineByIdUseCase($timelineRepo),
    new CreateTimelineUseCase($timelineRepo, $userRepo),
    new UpdateTimelineUseCase($timelineRepo, $userRepo),
    new DeleteTimelineUseCase($timelineRepo),
);


$userController = new UserController(
    new GetAllUsersUseCase($userRepo),
    new GetUserByIdUseCase($userRepo),
    new CreateUserUseCase($userRepo),
    new UpdateUserUseCase($userRepo),
    new DeleteUserUseCase($userRepo),
);


$authController = new AuthController(
    new LoginUseCase($authRepo),
    new RegisterUseCase($authRepo)
);

// Parse URI
$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($requestUri, '/'));

$resource = $segments[1] ?? null;
$id = $_GET['id'] ?? null;
$body = json_decode(file_get_contents("php://input"), true);

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
    'auth' => function ($method, $id, $body) use ($segments, $authController) {
        if ($method !== 'POST') {
            sendError("Method not allowed", 405);
        }

        $action = $segments[2] ?? null;

        return match ($action) {
            'login'    => $authController->login($body),
            'register' => $authController->register($body),
            default    => sendError("Missing or invalid action", 400),
        };
    },


];

// Ensure the route is valid and within /api/*
if ($segments[0] !== 'api' || !isset($routes[$resource])) {
    sendError("Route not found", 404);
}

$routes[$resource]($requestMethod, $id, $body);
