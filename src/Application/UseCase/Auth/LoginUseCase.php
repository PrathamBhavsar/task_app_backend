<?php

namespace Application\UseCase\Auth;

use Domain\Repository\UserRepositoryInterface;
use Infrastructure\Auth\JwtService;

class LoginUseCase
{
    public function __construct(private UserRepositoryInterface $userRepo, private JwtService $jwtService) {}

    public function execute(string $email, string $password): ?array
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user || !password_verify($password, $user->getPassword())) return null;

        $token = $this->jwtService->generateToken($user);
        return ['user' => $user->jsonSerialize(), 'token' => $token];
    }
}
