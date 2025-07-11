<?php

namespace Domain\Repository;

use Domain\Entity\QuoteMeasurement;

interface QuoteMeasurementRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?QuoteMeasurement;
    public function save(QuoteMeasurement $quoteMeasurement): QuoteMeasurement;
    public function delete(QuoteMeasurement $quoteMeasurement): void;
}
