<?php

use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\MeasurementRepository;
use Interface\Controller\MeasurementController;
use Interface\Http\JsonResponse;
use Application\UseCase\Measurement\{
    GetAllMeasurementsUseCase,
    GetMeasurementByIdUseCase,
    CreateMeasurementUseCase,
    UpdateMeasurementUseCase,
    DeleteMeasurementUseCase
};

function handleMeasurementRoutes(string $method)
{
    $em = EntityManagerFactory::create();
    $repo = new MeasurementRepository($em);
    $controller = new MeasurementController(
        new GetAllMeasurementsUseCase($repo),
        new GetMeasurementByIdUseCase($repo),
        new CreateMeasurementUseCase($repo),
        new UpdateMeasurementUseCase($repo),
        new DeleteMeasurementUseCase($repo),
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
