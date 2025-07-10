<?php

namespace Application\UseCase\Task;

use Domain\Repository\TaskRepositoryInterface;
use Domain\Entity\Task;

class GetTaskByIdUseCase
{
    public function __construct(private TaskRepositoryInterface $repo) {}

    public function execute(int $id): ?Task
    {
        return $this->repo->findById($id);
    }
}
