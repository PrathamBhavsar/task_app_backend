<?php

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
    $em = EntityManagerFactory::create();
    $repo = new TaskRepository($em);
    $controller = new TaskController(
        new GetAllTasksUseCase($repo),
        new GetTaskByIdUseCase($repo),
        new CreateTaskUseCase($repo),
        new UpdateTaskUseCase($repo),
        new UpdateTaskStatusUseCase($repo),
        new DeleteTaskUseCase($repo),
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
