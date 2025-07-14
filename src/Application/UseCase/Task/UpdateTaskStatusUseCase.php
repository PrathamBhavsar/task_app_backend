<?php

namespace Application\UseCase\Task;

use Domain\Repository\TaskRepositoryInterface;
use Domain\Entity\Task;

class UpdateTaskStatusUseCase
{
    public function __construct(private TaskRepositoryInterface $repo) {}

    public function execute(int $id, string $status): ?Task
    {
        $task = $this->repo->findById($id);
        if (!$task) return null;

        $task->setStatus($status);
        return $this->repo->save($task);
    }
}
