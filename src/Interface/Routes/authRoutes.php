<?php

use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\AuthRepository;
use Interface\Controller\AuthController;
use Application\UseCase\Auth\{
    LoginUseCase,
    RegisterUseCase
};
use Interface\Http\JsonResponse;

function handleAuthRoutes(string $method): void
{
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $_GET['action'] ?? null;

    $em = EntityManagerFactory::create();
    $repo = new AuthRepository($em);

    $authController = new AuthController(
        new LoginUseCase($repo),
        new RegisterUseCase($repo)
    );

    if ($method !== 'POST') {
        echo JsonResponse::error("Method not allowed", 405);
        return;
    }

    switch ($action) {
        case 'login':
            echo $authController->login($data);
            break;

        case 'register':
            echo $authController->register($data);
            break;

        default:
            echo JsonResponse::error("Missing or invalid 'action' parameter", 400);
    }
}
