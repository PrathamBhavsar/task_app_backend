<?php

namespace Application\UseCase\QuoteMeasurement;

use Domain\Repository\QuoteMeasurementRepositoryInterface;
use Domain\Entity\QuoteMeasurement;

class GetQuoteMeasurementByIdUseCase
{
    public function __construct(private QuoteMeasurementRepositoryInterface $repo) {}

    public function execute(int $id): ?QuoteMeasurement
    {
        return $this->repo->findById($id);
    }
}
