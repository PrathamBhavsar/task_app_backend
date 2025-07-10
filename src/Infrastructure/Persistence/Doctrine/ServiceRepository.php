<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\Service;
use Domain\Repository\ServiceRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(Service::class)->findAll();
    }

    public function findAllByTaskId(int $taskId): array
    {
        return $this->em->getRepository(Service::class)->findBy([
            'task_id' => $taskId
        ]);
    }

    public function findById(int $id): ?Service
    {
        return $this->em->getRepository(Service::class)->find($id);
    }

    public function save(Service $service): Service
    {
        $this->em->persist($service);
        $this->em->flush();
        return $service;
    }

    public function delete(Service $service): void
    {
        $this->em->remove($service);
        $this->em->flush();
    }
}
