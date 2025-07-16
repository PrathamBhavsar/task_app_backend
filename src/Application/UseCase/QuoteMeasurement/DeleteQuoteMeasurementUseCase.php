<?php

namespace Application\UseCase\QuoteMeasurement;

use Domain\Repository\QuoteMeasurementRepositoryInterface;
use Domain\Repository\QuoteRepositoryInterface;

class DeleteQuoteMeasurementUseCase
{
    public function __construct(
        private QuoteMeasurementRepositoryInterface $repo,
        private QuoteRepositoryInterface $quoteRepo
    ) {}

    public function execute(int $id): void
    {
        $quoteMeasurement = $this->repo->findById($id);
        if (!$quoteMeasurement) return;

        $this->repo->delete($quoteMeasurement);
    }
}
