<?php

namespace Application\UseCase\Measurement;

use Domain\Entity\Measurement;
use Domain\Repository\MeasurementRepositoryInterface;

class CreateMeasurementUseCase
{
    public function __construct(private MeasurementRepositoryInterface $repo) {}

    public function execute(array $data): Measurement
    {

        $measurement = new Measurement(
            taskId: $data['task_id'],
            location: $data['location'],
            width: $data['width'],
            height: $data['height'],
            area: $data['area'],
            unit: $data['unit'],
            notes: $data['notes'],
        );

        return $this->repo->save($measurement);
    }
}
