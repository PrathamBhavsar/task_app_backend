<?php

namespace Domain\Repository;

use Domain\Entity\Timeline;

interface TimelineRepositoryInterface
{
    public function findAllByTaskId(int $taskId): array;
    public function findAll(): array;
    public function findById(int $id): ?Timeline;
    public function save(Timeline $timeline): Timeline;
    public function delete(Timeline $timeline): void;
}
