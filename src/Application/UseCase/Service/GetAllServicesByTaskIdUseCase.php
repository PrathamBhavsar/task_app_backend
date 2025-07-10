<?php

namespace Application\UseCase\Service;

use Domain\Repository\ServiceRepositoryInterface;

class GetAllServicesByTaskIdUseCase
{
    public function __construct(private ServiceRepositoryInterface $repo) {}

    public function execute(int $taskId): array
    {
        return $this->repo->findAllByTaskId($taskId);
    }
}
