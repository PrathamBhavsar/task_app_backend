<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\Timeline;
use Domain\Repository\TimelineRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class TimelineRepository implements TimelineRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}


    public function findAll(): array
    {
        return $this->em->getRepository(Timeline::class)->findAll();
    }

    public function findAllByTaskId(int $taskId): array
    {
        return $this->em->getRepository(Timeline::class)->findBy([
            'task_id' => $taskId
        ]);
    }

    public function findById(int $id): ?Timeline
    {
        return $this->em->getRepository(Timeline::class)->find($id);
    }

    public function save(Timeline $timeline): Timeline
    {
        $this->em->persist($timeline);
        $this->em->flush();
        return $timeline;
    }

    public function delete(Timeline $timeline): void
    {
        $this->em->remove($timeline);
        $this->em->flush();
    }
}
