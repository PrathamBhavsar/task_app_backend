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

        // Use user_id as created_by
        $userId = $data['user_id'] ?? $data['created_by'] ?? null;
        if (!$userId) {
            throw new \InvalidArgumentException('User ID is required');
        }

        $createdBy = $this->em->getReference(User::class, $userId);
        $client = $this->em->getReference(Client::class, $data['client_id']);
        $designer = $this->em->getReference(Designer::class, $data['designer_id']);
        
        // Handle optional agency
        $agency = null;
        if (isset($data['agency_id']) && $data['agency_id']) {
            $agency = $this->em->getReference(User::class, $data['agency_id']);
        }

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
            agency: $agency
        );

        $task = $this->repo->save($task);

        // Create timeline entry
        $timeline = new \Domain\Entity\Timeline(
            $task->getId(),
            $data['status'],
            $createdBy
        );

        // Save timeline entry
        $this->timelineRepo->save($timeline);

        return $task;
    }
}
