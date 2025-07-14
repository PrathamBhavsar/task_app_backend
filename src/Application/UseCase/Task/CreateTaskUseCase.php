<?php

namespace Application\UseCase\Task;

use Domain\Entity\Task;
use Domain\Repository\TaskRepositoryInterface;

class CreateTaskUseCase
{
    public function __construct(private TaskRepositoryInterface $repo) {}

    public function execute(array $data): Task
    {

        $task = new Task(
            dealNo: $data['deal_no'],
            name: $data['name'],
            dueDate: $data['due_date'],
            priority: $data['priority'],
            remarks: $data['remarks'],
            createdBy: $data['created_by'],
            status: $data['status'],
            client: $data['client'],
            designer: $data['designer'],
            agency: $data['agency'],
        );

        return $this->repo->save($task);
    }
}
