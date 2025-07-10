<?php

namespace Application\UseCase\Measurement;

use Domain\Repository\MeasurementRepositoryInterface;
use Domain\Entity\Measurement;

class UpdateMeasurementUseCase
{
    public function __construct(private MeasurementRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?Measurement
    {
        $measurement = $this->repo->findById($id);
        if (!$measurement) return null;

        $measurement->setTaskId($data['task_id']);
        $measurement->setLocation($data['location']);
        $measurement->setWidth($data['width']);
        $measurement->setHeight($data['height']);
        $measurement->setArea($data['area']);
        $measurement->setUnit($data['unit']);
        $measurement->setNotes($data['notes']);

        return $this->repo->save($measurement);
    }
}
