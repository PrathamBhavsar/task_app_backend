<?php

namespace Application\UseCase\Bill;

use Domain\Repository\BillRepositoryInterface;
use Domain\Entity\Bill;

class UpdateBillUseCase
{
    public function __construct(private BillRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?Bill
    {
        $bill = $this->repo->findById($id);
        if (!$bill) return null;

        $bill->setName($data['name']);
        $bill->setContactNo($data['contact_no']);
        $bill->setAddress($data['address']);
        $bill->setFirmName($data['firm_name']);
        $bill->setProfileBgColor($data['profile_bg_color']);

        return $this->repo->save($bill);
    }
}
