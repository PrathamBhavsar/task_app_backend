<?php

namespace Application\UseCase\Measurement;

use Domain\Repository\MeasurementRepositoryInterface;

class GetAllMeasurementsByTaskIdUseCase
{
    public function __construct(private MeasurementRepositoryInterface $repo) {}

    public function execute(int $taskId): array
    {
        return $this->repo->findAllByTaskId($taskId);
    }
}
