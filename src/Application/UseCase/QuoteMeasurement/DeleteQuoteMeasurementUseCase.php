<?php

namespace Application\UseCase\QuoteMeasurement;

use Domain\Repository\QuoteMeasurementRepositoryInterface;

class DeleteQuoteMeasurementUseCase
{
    public function __construct(private QuoteMeasurementRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $quoteMeasurement = $this->repo->findById($id);
        if ($quoteMeasurement) {
            $this->repo->delete($quoteMeasurement);
        }
    }
}
