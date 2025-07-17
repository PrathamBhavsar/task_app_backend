<?php

use Infrastructure\Auth\JwtService;
use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\TaskRepository;
use Interface\Controller\TaskController;
use Interface\Http\JsonResponse;
use Application\UseCase\Task\{
    GetAllTasksUseCase,
    GetTaskByIdUseCase,
    CreateTaskUseCase,
    UpdateTaskUseCase,
    UpdateTaskStatusUseCase,
    DeleteTaskUseCase
};

function handleTaskRoutes(string $method)
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? null;

    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        echo JsonResponse::error("Unauthorized: Token missing", 401);
        return;
    }

    $token = str_replace('Bearer ', '', $authHeader);
    $jwtService = new JwtService();

    try {
        $payload = $jwtService->verifyToken($token);
    } catch (\Exception $e) {
        echo JsonResponse::error("Unauthorized: Invalid token", 401);
        return;
    }

    $userId = $payload['user_id'] ?? null;
    if (!$userId) {
        echo JsonResponse::error("Unauthorized: Invalid user", 401);
        return;
    }

    $em = EntityManagerFactory::create();
    $repo = new TaskRepository($em);

    // Optionally: pass $userId to use cases if task filtering is user-specific
    $controller = new TaskController(
        new GetAllTasksUseCase($repo),
        new GetTaskByIdUseCase($repo),
        new CreateTaskUseCase($repo),
        new UpdateTaskUseCase($repo),
        new UpdateTaskStatusUseCase($repo),
        new DeleteTaskUseCase($repo),
        $jwtService
    );

    $id = $_GET['id'] ?? null;
    $data = json_decode(file_get_contents("php://input"), true);

    switch ($method) {
        case 'GET':
            echo $id ? $controller->show((int) $id) : $controller->index();
            break;

        case 'POST':
            echo $controller->store($data);
            break;

        case 'PUT':
            if (!$id) {
                echo JsonResponse::error("ID required for update", 400);
                return;
            }
            echo $controller->update((int) $id, $data);
            break;

        case 'DELETE':
            if (!$id) {
                echo JsonResponse::error("ID required for delete", 400);
                return;
            }
            echo $controller->delete((int) $id);
            break;

        default:
            echo JsonResponse::error("Method not allowed", 405);
    }
}
