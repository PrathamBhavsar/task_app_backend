<?php

namespace Application\UseCase\Measurement;

use Domain\Entity\Measurement;
use Domain\Repository\MeasurementRepositoryInterface;

class CreateMeasurementUseCase
{
    public function __construct(
        private MeasurementRepositoryInterface $repo,
    ) {}

    public function execute(array $list): array
    {
        $savedMeasurements = [];

        foreach ($list as $data) {
            $measurement = new Measurement(
                taskId: $data['task_id'],
                location: $data['location'],
                width: $data['width'],
                height: $data['height'],
                area: $data['area'],
                unit: $data['unit'],
                quantity: $data['quantity'] ?? 1,
                discount: $data['discount'] ?? 0,
                unitPrice: $data['unit_price'] ?? 0,
                totalPrice: $data['total_price'] ?? 0,
                notes: $data['notes'] ?? null,
            );

            $savedMeasurements[] = $this->repo->save($measurement);
        }

        return $savedMeasurements;
    }
}
