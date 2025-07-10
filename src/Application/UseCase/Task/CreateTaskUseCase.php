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
            name: $data['name'],
            contactNo: $data['contact_no'],
            address: $data['address'],
            firmName: $data['firm_name'],
            profileBgColor: $data['profile_bg_color']
        );

        return $this->repo->save($task);
    }
}
