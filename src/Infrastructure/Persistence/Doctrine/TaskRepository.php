<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\Task;
use Domain\Repository\TaskRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class TaskRepository implements TaskRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(Task::class)->findAll();
    }

    public function findById(int $id): ?Task
    {
        return $this->em->getRepository(Task::class)->find($id);
    }

    public function save(Task $task): Task
    {
        $this->em->persist($task);
        $this->em->flush();
        return $task;
    }

    public function delete(Task $task): void
    {
        $this->em->remove($task);
        $this->em->flush();
    }
}
