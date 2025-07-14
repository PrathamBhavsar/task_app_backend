<?php

namespace Application\UseCase\Task;

use Domain\Repository\TaskRepositoryInterface;
use Domain\Entity\Task;

class UpdateTaskUseCase
{
    public function __construct(private TaskRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?Task
    {
        $task = $this->repo->findById($id);
        if (!$task) return null;

        $task->setName($data['name']);
        $task->setDealNo($data['deal_no']);
        $task->setStartDate($data['start_date']);
        $task->setDueDate($data['due_date']);
        $task->setPriority($data['priority']);
        $task->setRemarks($data['remarks']);
        $task->setStatus($data['status']);
        $task->setClient($data['client']);
        $task->setDesigner($data['designer']);
        $task->setAgency($data['agency']);

        return $this->repo->save($task);
    }
}
