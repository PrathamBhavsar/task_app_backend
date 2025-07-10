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
        $task->setContactNo($data['contact_no']);
        $task->setAddress($data['address']);
        $task->setFirmName($data['firm_name']);
        $task->setProfileBgColor($data['profile_bg_color']);

        return $this->repo->save($task);
    }
}
