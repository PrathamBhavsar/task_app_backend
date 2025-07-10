<?php

namespace Domain\Repository;

use Domain\Entity\Measurement;

interface MeasurementRepositoryInterface
{
    public function findAll(): array;
    public function findAllByTaskId(int $taskId): array;
    public function findById(int $id): ?Measurement;
    public function save(Measurement $measurement): Measurement;
    public function delete(Measurement $measurement): void;
}
