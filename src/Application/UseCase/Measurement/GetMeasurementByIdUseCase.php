<?php

namespace Application\UseCase\Measurement;

use Domain\Repository\MeasurementRepositoryInterface;
use Domain\Entity\Measurement;

class GetMeasurementByIdUseCase
{
    public function __construct(private MeasurementRepositoryInterface $repo) {}

    public function execute(int $id): ?Measurement
    {
        return $this->repo->findById($id);
    }
}
