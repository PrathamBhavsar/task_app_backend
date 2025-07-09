<?php

namespace Application\UseCase\TaskMessage;

use Domain\Repository\TaskMessageRepositoryInterface;
use Domain\Entity\TaskMessage;

class GetTaskMessageByIdUseCase
{
    public function __construct(private TaskMessageRepositoryInterface $repo) {}

    public function execute(int $id): ?TaskMessage
    {
        return $this->repo->findById($id);
    }
}
