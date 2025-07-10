<?php

namespace Application\UseCase\Task;

use Domain\Repository\TaskRepositoryInterface;

class DeleteTaskUseCase
{
    public function __construct(private TaskRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $task = $this->repo->findById($id);
        if ($task) {
            $this->repo->delete($task);
        }
    }
}
