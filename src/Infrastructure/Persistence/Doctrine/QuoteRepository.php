<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\Quote;
use Domain\Repository\QuoteRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class QuoteRepository implements QuoteRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(Quote::class)->findAll();
    }

    public function findByTaskId(int $taskId): Quote
    {
        return $this->em->getRepository(Quote::class)
            ->findOneBy(['task_id' => $taskId]);
    }


    public function findById(int $id): ?Quote
    {
        return $this->em->getRepository(Quote::class)->find($id);
    }

    public function save(Quote $quote): Quote
    {
        $this->em->persist($quote);
        $this->em->flush();
        return $quote;
    }

    public function delete(Quote $quote): void
    {
        $this->em->remove($quote);
        $this->em->flush();
    }
}
