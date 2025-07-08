<?php

use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\ClientRepository;
use Interface\Controller\ClientController;
use Interface\Http\JsonResponse;
use Application\UseCase\Client\{
    GetAllClientsUseCase,
    GetClientByIdUseCase,
    CreateClientUseCase,
    UpdateClientUseCase,
    DeleteClientUseCase
};

function handleClientRoutes(string $method)
{
    $em = EntityManagerFactory::create();
    $repo = new ClientRepository($em);
    $controller = new ClientController(
        new GetAllClientsUseCase($repo),
        new GetClientByIdUseCase($repo),
        new CreateClientUseCase($repo),
        new UpdateClientUseCase($repo),
        new DeleteClientUseCase($repo),
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
