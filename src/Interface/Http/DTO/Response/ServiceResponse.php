<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for service data
 */
class ServiceResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $task_service_id,
        public readonly int $task_id,
        public readonly object $service_master,
        public readonly int $quantity,
        public readonly float $unit_price,
        public readonly float $total_amount
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $service
     * @return static
     */
    public static function fromEntity(object $service): static
    {
        return new self(
            task_service_id: $service->getId(),
            task_id: $service->getTaskId(),
            service_master: $service->getServiceMaster(),
            quantity: $service->getQuantity(),
            unit_price: $service->getUnitPrice(),
            total_amount: $service->getTotalAmount()
        );
    }
}
