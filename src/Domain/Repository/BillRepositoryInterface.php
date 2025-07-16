<?php

namespace Domain\Repository;

use Domain\Entity\Bill;

interface BillRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Bill;
    public function save(Bill $bill): Bill;
    public function delete(Bill $bill): void;
}
