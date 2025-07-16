<?php

namespace Application\UseCase\Measurement;

use Domain\Entity\Measurement;
use Domain\Repository\MeasurementRepositoryInterface;

class CreateMeasurementUseCase
{
    public function __construct(
        private MeasurementRepositoryInterface $repo,
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
            quantity: $data['quantity'],
            discount: $data['discount'],
            unitPrice: $data['unit_price'],
            totalPrice: $data['total_price'],
            notes: $data['notes'] ?? null,
        );

        $saved = $this->repo->save($measurement);
        return $saved;
    }
}
