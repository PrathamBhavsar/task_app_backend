<?php

namespace Application\UseCase\User;

use Domain\Repository\UserRepositoryInterface;

class GetAllUsersUseCase
{
    public function __construct(private UserRepositoryInterface $repo) {}

    public function execute(): array
    {
        return $this->repo->findAll();
    }
}
