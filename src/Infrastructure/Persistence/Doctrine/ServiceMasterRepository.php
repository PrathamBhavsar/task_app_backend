<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\ServiceMaster;
use Domain\Repository\ServiceMasterRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class ServiceMasterRepository implements ServiceMasterRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(ServiceMaster::class)->findAll();
    }

    public function findById(int $id): ?ServiceMaster
    {
        return $this->em->getRepository(ServiceMaster::class)->find($id);
    }

    public function save(ServiceMaster $serviceMaster): ServiceMaster
    {
        $this->em->persist($serviceMaster);
        $this->em->flush();
        return $serviceMaster;
    }

    public function delete(ServiceMaster $serviceMaster): void
    {
        $this->em->remove($serviceMaster);
        $this->em->flush();
    }
}
