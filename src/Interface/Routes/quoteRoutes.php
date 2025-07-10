<?php

use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\QuoteRepository;
use Interface\Controller\QuoteController;
use Interface\Http\JsonResponse;
use Application\UseCase\Quote\{
    GetAllQuotesUseCase,
    GetQuoteByIdUseCase,
    CreateQuoteUseCase,
    UpdateQuoteUseCase,
    DeleteQuoteUseCase
};

function handleQuoteRoutes(string $method)
{
    $em = EntityManagerFactory::create();
    $repo = new QuoteRepository($em);
    $controller = new QuoteController(
        new GetAllQuotesUseCase($repo),
        new GetQuoteByIdUseCase($repo),
        new CreateQuoteUseCase($repo),
        new UpdateQuoteUseCase($repo),
        new DeleteQuoteUseCase($repo),
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
