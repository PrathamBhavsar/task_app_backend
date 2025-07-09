<?php

namespace Application\UseCase\TaskMessage;

use Domain\Repository\TaskMessageRepositoryInterface;
use Domain\Entity\TaskMessage;

class UpdateTaskMessageUseCase
{
    public function __construct(private TaskMessageRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?TaskMessage
    {
        $taskMessage = $this->repo->findById($id);
        if (!$taskMessage) return null;

        $taskMessage->setName($data['name']);
        $taskMessage->setContactNo($data['contact_no']);
        $taskMessage->setAddress($data['address']);
        $taskMessage->setFirmName($data['firm_name']);
        $taskMessage->setProfileBgColor($data['profile_bg_color']);

        return $this->repo->save($taskMessage);
    }
}
