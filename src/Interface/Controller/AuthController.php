<?php

namespace Interface\Controller;

use Application\UseCase\Auth\{
    LoginUseCase,
    RegisterUseCase
};
use Interface\Http\JsonResponse;
use Domain\Entity\User;

class AuthController
{
    public function __construct(
        private LoginUseCase $login,
        private RegisterUseCase $register,
    ) {}

    public function login(array $data)
    {
        $result = $this->login->execute($data['email'], $data['password']);

        return $result
            ? JsonResponse::ok($result)
            : JsonResponse::error("Invalid credentials", 401);
    }

    public function register(array $data)
    {
        $user = $this->register->execute($data);
        return $user
            ? JsonResponse::ok($user->jsonSerialize())
            : JsonResponse::error("Email already registered", 400);
    }
}
