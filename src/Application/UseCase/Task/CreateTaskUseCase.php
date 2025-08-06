<?php

namespace Application\UseCase\Task;

use Domain\Entity\Task;
use Domain\Entity\User;
use Domain\Entity\Client;
use Domain\Entity\Designer;
use Domain\Repository\TaskRepositoryInterface;
use Application\Service\DealNumberGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Domain\Repository\TimelineRepositoryInterface;
use Timeline;

class CreateTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $repo,
        private DealNumberGeneratorService $dealNumberService,
        private EntityManagerInterface $em,
        private TimelineRepositoryInterface $timelineRepo

    ) {}

    public function execute(array $data): Task
    {
        $dealNo = $this->dealNumberService->generate();
        $dueDate = new \DateTime($data['due_date']);

        $createdBy = $this->em->getReference(User::class, $data['created_by']);
        $client = $this->em->getReference(Client::class, $data['client_id']);
        $designer = $this->em->getReference(Designer::class, $data['designer_id']);

        $task = new Task(
            dealNo: $dealNo,
            name: $data['name'],
            dueDate: $dueDate,
            priority: $data['priority'],
            remarks: $data['remarks'],
            createdBy: $createdBy,
            status: $data['status'],
            client: $client,
            designer: $designer,
            agency: null
        );

        $task = $this->repo->save($task);

        // Get user reference
        $user = $this->em->getReference(User::class, $data['created_by']);

        // Create timeline entry
        $timeline = new \Domain\Entity\Timeline(
            $task->getId(),
            $data['status'],
            $user
        );

        // Save timeline entry
        $this->timelineRepo->save($timeline);

        return $task;
    }
}
