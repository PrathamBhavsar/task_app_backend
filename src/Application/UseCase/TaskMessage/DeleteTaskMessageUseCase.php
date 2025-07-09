<?php

namespace Application\UseCase\TaskMessage;

use Domain\Repository\TaskMessageRepositoryInterface;

class DeleteTaskMessageUseCase
{
    public function __construct(private TaskMessageRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $taskMessage = $this->repo->findById($id);
        if ($taskMessage) {
            $this->repo->delete($taskMessage);
        }
    }
}
