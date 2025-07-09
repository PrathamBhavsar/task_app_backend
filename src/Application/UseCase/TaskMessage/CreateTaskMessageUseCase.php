<?php

namespace Application\UseCase\TaskMessage;

use Domain\Entity\TaskMessage;
use Domain\Repository\TaskMessageRepositoryInterface;

class CreateTaskMessageUseCase
{
    public function __construct(private TaskMessageRepositoryInterface $repo) {}

    public function execute(array $data): TaskMessage
    {

        $taskMessage = new TaskMessage(
            name: $data['name'],
            contactNo: $data['contact_no'],
            address: $data['address'],
            firmName: $data['firm_name'],
            profileBgColor: $data['profile_bg_color']
        );

        return $this->repo->save($taskMessage);
    }
}
