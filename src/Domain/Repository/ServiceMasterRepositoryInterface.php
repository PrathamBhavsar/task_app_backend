<?php

namespace Domain\Repository;

use Domain\Entity\ServiceMaster;

interface ServiceMasterRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?ServiceMaster;
    public function save(ServiceMaster $serviceMaster): ServiceMaster;
    public function delete(ServiceMaster $serviceMaster): void;
}
