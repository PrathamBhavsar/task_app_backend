<?php

namespace Application\UseCase\Bill;

use Domain\Entity\Bill;
use Domain\Repository\BillRepositoryInterface;

class CreateBillUseCase
{
    public function __construct(private BillRepositoryInterface $repo) {}

    public function execute(array $data): Bill
    {

        $bill = new Bill(
            name: $data['name'],
            contactNo: $data['contact_no'],
            address: $data['address'],
            firmName: $data['firm_name'],
            profileBgColor: $data['profile_bg_color']
        );

        return $this->repo->save($bill);
    }
}
