<?php

namespace Application\UseCase\QuoteMeasurement;

use Domain\Entity\QuoteMeasurement;
use Domain\Repository\QuoteMeasurementRepositoryInterface;

class CreateQuoteMeasurementUseCase
{
    public function __construct(private QuoteMeasurementRepositoryInterface $repo) {}

    public function execute(array $data): QuoteMeasurement
    {

        $quoteMeasurement = new QuoteMeasurement(
            quoteId: $data['quote_id'],
            measurementId: $data['measurement_it'],
            quantity: $data['quantity'],
            unitPrice: $data['unit_price'],
            totalPrice: $data['total_price']
        );

        return $this->repo->save($quoteMeasurement);
    }
}
