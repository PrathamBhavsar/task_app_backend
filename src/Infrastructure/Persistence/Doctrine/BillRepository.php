<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\Bill;
use Domain\Repository\BillRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class BillRepository implements BillRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(Bill::class)->findAll();
    }

    public function findById(int $id): ?Bill
    {
        return $this->em->getRepository(Bill::class)->find($id);
    }

    public function findByTaskId(int $taskId): ?Bill
    {
        return $this->em->getRepository(Bill::class)
            ->findOneBy(['task_id' => $taskId]);
    }

    public function save(Bill $bill): Bill
    {
        $this->em->persist($bill);
        $this->em->flush();
        return $bill;
    }

    public function delete(Bill $bill): void
    {
        $this->em->remove($bill);
        $this->em->flush();
    }
}
