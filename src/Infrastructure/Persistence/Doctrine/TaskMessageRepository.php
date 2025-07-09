<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\TaskMessage;
use Domain\Repository\TaskMessageRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class TaskMessageRepository implements TaskMessageRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(TaskMessage::class)->findAll();
    }

    public function findAllByTaskId(int $taskId): array
    {
        return $this->em->getRepository(TaskMessage::class)->findBy([
            'task_id' => $taskId
        ]);
    }

    public function findById(int $id): ?TaskMessage
    {
        return $this->em->getRepository(TaskMessage::class)->find($id);
    }

    public function save(TaskMessage $taskMessage): TaskMessage
    {
        $this->em->persist($taskMessage);
        $this->em->flush();
        return $taskMessage;
    }

    public function delete(TaskMessage $taskMessage): void
    {
        $this->em->remove($taskMessage);
        $this->em->flush();
    }
}
