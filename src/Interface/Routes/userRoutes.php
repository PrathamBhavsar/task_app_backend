<?php

use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\UserRepository;
use Interface\Controller\UserController;
use Interface\Http\JsonResponse;
use Application\UseCase\User\{
    GetAllUsersUseCase,
    GetUserByIdUseCase,
    CreateUserUseCase,
    UpdateUserUseCase,
    DeleteUserUseCase
};

function handleUserRoutes(string $method)
{
    $em = EntityManagerFactory::create();
    $repo = new UserRepository($em);

    $controller = new UserController(
        new GetAllUsersUseCase($repo),
        new GetUserByIdUseCase($repo),
        new CreateUserUseCase($repo),
        new UpdateUserUseCase($repo),
        new DeleteUserUseCase($repo),
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
