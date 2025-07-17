<?php

use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\AuthRepository;

use Domain\Repository\UserRepositoryInterface;
use Interface\Controller\AuthController;
use Application\UseCase\Auth\{
    LoginUseCase,
    RegisterUseCase
};
use Interface\Http\JsonResponse;
use Infrastructure\Auth\JwtService;


function handleAuthRoutes(string $method): void
{
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $_GET['action'] ?? null;

    $em = EntityManagerFactory::create();
    $repo = new UserRepositoryInterface($em);
    $authRepo = new AuthRepository($em);

    $jwtService = new JwtService($em);

    $authController = new AuthController(
        new LoginUseCase($repo, $jwtService),
        new RegisterUseCase($authRepo)

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
