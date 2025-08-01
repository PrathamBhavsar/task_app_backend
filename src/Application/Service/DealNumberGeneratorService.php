<?php

namespace Application\Service;

use Domain\Repository\ConfigRepositoryInterface;

class DealNumberGeneratorService
{
    public function __construct(
        private ConfigRepositoryInterface $configRepo
    ) {}

    public function generate(): string
    {
        $latest = $this->configRepo->get('latest_deal_no') ?? '0000-0000';

        [$prefix, $suffix] = explode('-', $latest);
        $prefixNum = (int) $prefix;
        $suffixNum = (int) $suffix;

        if ($suffixNum < 9999) {
            $suffixNum++;
        } else {
            $suffixNum = 0;
            $prefixNum++;
        }

        $newDealNo = str_pad((string) $prefixNum, 4, '0', STR_PAD_LEFT)
            . '-' .
            str_pad((string) $suffixNum, 4, '0', STR_PAD_LEFT);

        $this->configRepo->set('latest_deal_no', $newDealNo);

        return $newDealNo;
    }
}
