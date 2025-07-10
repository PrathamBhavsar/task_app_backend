<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\Measurement;
use Domain\Repository\MeasurementRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class MeasurementRepository implements MeasurementRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(Measurement::class)->findAll();
    }

    public function findAllByTaskId(int $taskId): array
    {
        return $this->em->getRepository(Measurement::class)->findBy([
            'task_id' => $taskId
        ]);
    }

    public function findById(int $id): ?Measurement
    {
        return $this->em->getRepository(Measurement::class)->find($id);
    }

    public function save(Measurement $measurement): Measurement
    {
        $this->em->persist($measurement);
        $this->em->flush();
        return $measurement;
    }

    public function delete(Measurement $measurement): void
    {
        $this->em->remove($measurement);
        $this->em->flush();
    }
}
