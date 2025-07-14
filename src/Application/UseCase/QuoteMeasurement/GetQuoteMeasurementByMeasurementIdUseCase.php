<?php

namespace Application\UseCase\QuoteMeasurement;

use Domain\Entity\QuoteMeasurement;
use Domain\Repository\QuoteMeasurementRepositoryInterface;

class GetQuoteMeasurementByMeasurementIdUseCase
{
    public function __construct(private QuoteMeasurementRepositoryInterface $repo) {}

    public function execute(int $quoteId): QuoteMeasurement
    {
        return $this->repo->findByMeasurementId($quoteId);
    }
}
