<?php

namespace Application\UseCase\Measurement;

use Domain\Repository\MeasurementRepositoryInterface;
use Domain\Repository\QuoteMeasurementRepositoryInterface;
use Domain\Entity\Measurement;

class UpdateMeasurementUseCase
{
    public function __construct(
        private MeasurementRepositoryInterface $repo,
        private QuoteMeasurementRepositoryInterface $quoteMeasurementRepo
    ) {}

    public function execute(int $id, array $data): ?Measurement
    {
        $measurement = $this->repo->findById($id);
        if (!$measurement) return null;

        $measurement->setTaskId($data['task_id']);
        $measurement->setLocation($data['location']);
        $measurement->setWidth($data['width']);
        $measurement->setHeight($data['height']);
        $measurement->setArea($data['area']);
        $measurement->setUnit($data['unit']);
        $measurement->setNotes($data['notes']);

        $updated = $this->repo->save($measurement);

        $quoteMeasurement = $this->quoteMeasurementRepo->findByMeasurementId($id);
        if ($quoteMeasurement) {
            // You can update quote measurement logic here (example: recalculate total)
            $quoteMeasurement->setTotalPrice(
                $quoteMeasurement->getUnitPrice() * $quoteMeasurement->getQuantity()
            );

            $this->quoteMeasurementRepo->save($quoteMeasurement);
        }

        return $updated;
    }
}
