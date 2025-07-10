<?php

namespace Domain\Repository;

use Domain\Entity\Quote;

interface QuoteRepositoryInterface
{
    public function findAll(): array;
    public function findByTaskId(int $taskId): Quote;
    public function findById(int $id): ?Quote;
    public function save(Quote $quote): Quote;
    public function delete(Quote $quote): void;
}
