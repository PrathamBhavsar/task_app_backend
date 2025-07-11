<?php

namespace Application\UseCase\QuoteMeasurement;

use Domain\Repository\QuoteMeasurementRepositoryInterface;
use Domain\Entity\QuoteMeasurement;

class UpdateQuoteMeasurementUseCase
{
    public function __construct(private QuoteMeasurementRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?QuoteMeasurement
    {
        $quoteMeasurement = $this->repo->findById($id);
        if (!$quoteMeasurement) return null;

        $quoteMeasurement->setQuoteId($data['quote_id']);
        $quoteMeasurement->setMeasurementId($data['measurement_it']);
        $quoteMeasurement->setQuantity($data['quantity']);
        $quoteMeasurement->setUnitPrice($data['unit_price']);
        $quoteMeasurement->setTotalPrice($data['total_price']);

        return $this->repo->save($quoteMeasurement);
    }
}
