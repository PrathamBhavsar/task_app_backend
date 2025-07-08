<?php

namespace Application\UseCase\User;

use Domain\Repository\UserRepositoryInterface;

class DeleteUserUseCase
{
    public function __construct(private UserRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $user = $this->repo->findById($id);
        if ($user) {
            $this->repo->delete($user);
        }
    }
}
