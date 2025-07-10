<?php

namespace Domain\Repository;

use Domain\Entity\Task;

interface TaskRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Task;
    public function save(Task $task): Task;
    public function delete(Task $task): void;
}
