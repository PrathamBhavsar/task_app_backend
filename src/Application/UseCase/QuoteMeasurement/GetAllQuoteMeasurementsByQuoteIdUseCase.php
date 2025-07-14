<?php

namespace Application\UseCase\QuoteMeasurement;

use Domain\Repository\QuoteMeasurementRepositoryInterface;

class GetAllQuoteMeasurementsByQuoteIdUseCase
{
    public function __construct(private QuoteMeasurementRepositoryInterface $repo) {}

    public function execute(int $quoteId): array
    {
        return $this->repo->findAllByQuoteId($quoteId);
    }
}
