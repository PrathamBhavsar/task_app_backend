<?php

namespace Application\UseCase\Measurement;

use Domain\Entity\Measurement;
use Domain\Entity\QuoteMeasurement;
use Domain\Repository\MeasurementRepositoryInterface;
use Domain\Repository\QuoteMeasurementRepositoryInterface;
use Domain\Repository\QuoteRepositoryInterface;

class CreateMeasurementUseCase
{
    public function __construct(
        private MeasurementRepositoryInterface $repo,
        private QuoteMeasurementRepositoryInterface $quoteMeasurementRepo,
        private QuoteRepositoryInterface $quoteRepo
    ) {}

    public function execute(array $data): Measurement
    {
        $measurement = new Measurement(
            taskId: $data['task_id'],
            location: $data['location'],
            width: $data['width'],
            height: $data['height'],
            area: $data['area'],
            unit: $data['unit'],
            notes: $data['notes'] ?? null,
        );

        $saved = $this->repo->save($measurement);

        // Auto-create quote_measurement by looking up quote using task_id
        $quote = $this->quoteRepo->findByTaskId($data['task_id']);

        if ($quote) {
            $quoteMeasurement = new QuoteMeasurement(
                quoteId: $quote->getId(),
                measurementId: $saved->getId(),
                quantity: 1,
                unitPrice: 0.0,
                totalPrice: 0.0,
                discount: 0.0
            );

            $this->quoteMeasurementRepo->save($quoteMeasurement);
        }

        return $saved;
    }
}
