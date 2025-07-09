<?php

namespace Domain\Repository;

use Domain\Entity\TaskMessage;

interface TaskMessageRepositoryInterface
{
    public function findAllByTaskId(int $taskId): array;
    public function findAll(): array;
    public function findById(int $id): ?TaskMessage;
    public function save(TaskMessage $taskMessage): TaskMessage;
    public function delete(TaskMessage $taskMessage): void;
}
