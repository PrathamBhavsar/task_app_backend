<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for measurement data
 */
class MeasurementResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $measurement_id,
        public readonly int $task_id,
        public readonly string $location,
        public readonly float $width,
        public readonly float $height,
        public readonly float $area,
        public readonly string $unit,
        public readonly int $quantity,
        public readonly float $unit_price,
        public readonly float $discount,
        public readonly float $total_price,
        public readonly string $notes
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $measurement
     * @return static
     */
    public static function fromEntity(object $measurement): static
    {
        return new self(
            measurement_id: $measurement->getId(),
            task_id: $measurement->getTaskId(),
            location: $measurement->getLocation(),
            width: $measurement->getWidth(),
            height: $measurement->getHeight(),
            area: $measurement->getArea(),
            unit: $measurement->getUnit(),
            quantity: $measurement->getQuantity(),
            unit_price: $measurement->getUnitPrice(),
            discount: $measurement->getDiscount(),
            total_price: $measurement->getTotalPrice(),
            notes: $measurement->getNotes()
        );
    }
}
