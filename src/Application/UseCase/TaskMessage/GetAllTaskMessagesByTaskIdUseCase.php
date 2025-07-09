<?php

namespace Application\UseCase\TaskMessage;

use Domain\Repository\TaskMessageRepositoryInterface;

class GetAllTaskMessagesByTaskIdUseCase
{
    public function __construct(private TaskMessageRepositoryInterface $repo) {}

    public function execute(int $taskId): array
    {
        return $this->repo->findAllByTaskId($taskId);
    }
}
