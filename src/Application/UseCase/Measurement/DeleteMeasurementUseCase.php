<?php

namespace Application\UseCase\Measurement;

use Domain\Repository\MeasurementRepositoryInterface;
use Domain\Repository\QuoteMeasurementRepositoryInterface;

class DeleteMeasurementUseCase
{
    public function __construct(
        private MeasurementRepositoryInterface $measurementRepo,
        private QuoteMeasurementRepositoryInterface $quoteMeasurementRepo
    ) {}

    public function execute(int $id): void
    {
        $measurement = $this->measurementRepo->findById($id);
        if (!$measurement) return;

        $quoteMeasurement = $this->quoteMeasurementRepo->findByMeasurementId($id);

        $this->quoteMeasurementRepo->delete($quoteMeasurement);


        $this->measurementRepo->delete($measurement);
    }
}
