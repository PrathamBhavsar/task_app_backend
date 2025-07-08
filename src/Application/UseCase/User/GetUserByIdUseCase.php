<?php

namespace Application\UseCase\User;

use Domain\Repository\UserRepositoryInterface;
use Domain\Entity\User;

class GetUserByIdUseCase
{
    public function __construct(private UserRepositoryInterface $repo) {}

    public function execute(int $id): ?User
    {
        return $this->repo->findById($id);
    }
}
