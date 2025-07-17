<?php

namespace Application\UseCase\Auth;

use Domain\Repository\AuthRepositoryInterface;
use Infrastructure\Auth\JwtService;

class RegisterUseCase
{
    public function __construct(
        private AuthRepositoryInterface $repo,
        private JwtService $jwtService
    ) {}

    public function execute(array $data): ?array
    {
        if ($this->repo->emailExists($data['email'])) {
            return null;
        }

        $user = $this->repo->register($data);
        $token = $this->jwtService->generateToken($user);

        return ['user' => $user->jsonSerialize(), 'token' => $token];
    }
}
