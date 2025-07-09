<?php

namespace Application\UseCase\TaskMessage;

use Domain\Repository\TaskMessageRepositoryInterface;

class GetAllTaskMessagesUseCase
{
    public function __construct(private TaskMessageRepositoryInterface $repo) {}

    public function execute(): array
    {
        return $this->repo->findAll();
    }
}
