<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;

/**
 * Request DTO for updating a service
 * 
 * All fields are optional for partial updates
 */
class UpdateServiceRequest extends RequestDTO
{
    public function __construct(
        #[IntegerType, Min(1)]
        public readonly ?int $task_id = null,
        
        #[IntegerType, Min(1)]
        public readonly ?int $service_master_id = null,
        
        #[IntegerType, Min(1)]
        public readonly ?int $quantity = null,
        
        #[Min(0)]
        public readonly ?float $unit_price = null,
        
        #[Min(0)]
        public readonly ?float $total_amount = null
    ) {}
    
    /**
     * Create a DTO instance from request data
     * 
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new self(
            task_id: isset($data['task_id']) ? (int)$data['task_id'] : null,
            service_master_id: isset($data['service_master_id']) ? (int)$data['service_master_id'] : null,
            quantity: isset($data['quantity']) ? (int)$data['quantity'] : null,
            unit_price: isset($data['unit_price']) ? (float)$data['unit_price'] : null,
            total_amount: isset($data['total_amount']) ? (float)$data['total_amount'] : null
        );
    }
    
    /**
     * Get only the fields that were provided (not null)
     * 
     * @return array
     */
    public function getProvidedFields(): array
    {
        return array_filter($this->toArray(), fn($value) => $value !== null);
    }
}
