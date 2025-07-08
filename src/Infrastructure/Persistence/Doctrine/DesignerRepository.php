<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\Designer;
use Domain\Repository\DesignerRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DesignerRepository implements DesignerRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(Designer::class)->findAll();
    }

    public function findById(int $id): ?Designer
    {
        return $this->em->getRepository(Designer::class)->find($id);
    }

    public function save(Designer $designer): Designer
    {
        $this->em->persist($designer);
        $this->em->flush();
        return $designer;
    }

    public function delete(Designer $designer): void
    {
        $this->em->remove($designer);
        $this->em->flush();
    }
}
