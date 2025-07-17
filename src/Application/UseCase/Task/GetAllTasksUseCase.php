<?php

namespace Application\UseCase\Task;

use Domain\Repository\TaskRepositoryInterface;

class GetAllTasksUseCase
{
    public function __construct(private TaskRepositoryInterface $repo) {}

    public function execute(int $userId): array
    {
        return $this->repo->findByUserId($userId);
    }
}
