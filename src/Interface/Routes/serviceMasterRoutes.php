<?php

use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\ServiceMasterRepository;
use Interface\Controller\ServiceMasterController;
use Interface\Http\JsonResponse;
use Application\UseCase\ServiceMaster\{
    GetAllServiceMastersUseCase,
    GetServiceMasterByIdUseCase,
    CreateServiceMasterUseCase,
    UpdateServiceMasterUseCase,
    DeleteServiceMasterUseCase
};

function handleServiceMasterRoutes(string $method)
{
    $em = EntityManagerFactory::create();
    $repo = new ServiceMasterRepository($em);
    $controller = new ServiceMasterController(
        new GetAllServiceMastersUseCase($repo),
        new GetServiceMasterByIdUseCase($repo),
        new CreateServiceMasterUseCase($repo),
        new UpdateServiceMasterUseCase($repo),
        new DeleteServiceMasterUseCase($repo),
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
