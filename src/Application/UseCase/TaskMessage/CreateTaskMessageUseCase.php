<?php

namespace Application\UseCase\TaskMessage;

use Domain\Entity\TaskMessage;
use Domain\Repository\TaskMessageRepositoryInterface;

class CreateTaskMessageUseCase
{
    public function __construct(private TaskMessageRepositoryInterface $repo) {}

    public function execute(array $data): TaskMessage
    {
        $taskMessage = new TaskMessage(
            taskId: $data['task_id'],
            message: $data['message'],
            user: $data['user']
        );

        return $this->repo->save($taskMessage);
    }
}
