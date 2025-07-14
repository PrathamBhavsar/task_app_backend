<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\QuoteMeasurement;
use Domain\Repository\QuoteMeasurementRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class QuoteMeasurementRepository implements QuoteMeasurementRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(QuoteMeasurement::class)->findAll();
    }

    public function findById(int $id): ?QuoteMeasurement
    {
        return $this->em->getRepository(QuoteMeasurement::class)->find($id);
    }

    public function findAllByQuoteId(int $quoteId): array
    {
        return $this->em->getRepository(QuoteMeasurement::class)->findBy([
            'quote_id' => $quoteId
        ]);
    }

    public function findByMeasurementId(int $measurementId): ?QuoteMeasurement
    {
        return $this->em->getRepository(QuoteMeasurement::class)->findOneBy([
            'measurement_id' => $measurementId
        ]);
    }


    public function save(QuoteMeasurement $quoteMeasurement): QuoteMeasurement
    {
        $this->em->persist($quoteMeasurement);
        $this->em->flush();
        return $quoteMeasurement;
    }

    public function delete(QuoteMeasurement $quoteMeasurement): void
    {
        $this->em->remove($quoteMeasurement);
        $this->em->flush();
    }
}
