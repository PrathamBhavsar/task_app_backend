<?php

namespace Application\UseCase\Measurement;

use Domain\Repository\MeasurementRepositoryInterface;

class DeleteMeasurementUseCase
{
    public function __construct(
        private MeasurementRepositoryInterface $measurementRepo,
    ) {}

    public function execute(int $id): void
    {
        $measurement = $this->measurementRepo->findById($id);
        if (!$measurement) return;

        $this->measurementRepo->delete($measurement);
    }
}
