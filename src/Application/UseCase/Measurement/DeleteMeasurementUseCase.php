<?php

namespace Application\UseCase\Measurement;

use Domain\Repository\MeasurementRepositoryInterface;

class DeleteMeasurementUseCase
{
    public function __construct(private MeasurementRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $measurement = $this->repo->findById($id);
        if ($measurement) {
            $this->repo->delete($measurement);
        }
    }
}
