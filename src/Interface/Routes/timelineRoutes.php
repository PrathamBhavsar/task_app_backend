<?php

use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\TimelineRepository;
use Infrastructure\Persistence\Doctrine\UserRepository;
use Interface\Controller\TimelineController;
use Interface\Http\JsonResponse;
use Application\UseCase\Timeline\{
    GetAllTimelinesUseCase,
    GetAllTimelinesByTaskIdUseCase,
    GetTimelineByIdUseCase,
    CreateTimelineUseCase,
    UpdateTimelineUseCase,
    DeleteTimelineUseCase
};

function handleTimelineRoutes(string $method)
{
    $em = EntityManagerFactory::create();
    $repo = new TimelineRepository($em);
    $userRepo = new UserRepository($em);

    $controller = new TimelineController(
        new GetAllTimelinesUseCase($repo),
        new GetAllTimelinesByTaskIdUseCase($repo),
        new GetTimelineByIdUseCase($repo),
        new CreateTimelineUseCase($repo, $userRepo),
        new UpdateTimelineUseCase($repo, $userRepo),
        new DeleteTimelineUseCase($repo),
    );

    $id = $_GET['id'] ?? null;
    $taskId = $_GET['task_id'] ?? null;
    $data = json_decode(file_get_contents("php://input"), true);

    switch ($method) {
        case 'GET':
            if ($id) {
                echo $controller->show((int) $id);
            } elseif ($taskId) {
                echo $controller->getByTaskId((int) $taskId);
            } else {
                echo $controller->index();
            }
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
