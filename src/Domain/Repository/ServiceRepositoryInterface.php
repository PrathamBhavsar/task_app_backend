<?php

namespace Domain\Repository;

use Domain\Entity\Service;

interface ServiceRepositoryInterface
{
    public function findAll(): array;
    public function findAllByTaskId(int $taskId): array;
    public function findById(int $id): ?Service;
    public function save(Service $service): Service;
    public function delete(Service $service): void;
}
