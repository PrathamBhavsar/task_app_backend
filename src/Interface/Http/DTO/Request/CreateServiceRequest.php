<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Required;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;

/**
 * Request DTO for creating a service
 */
class CreateServiceRequest extends RequestDTO
{
    public function __construct(
        #[Required, IntegerType, Min(1)]
        public readonly int $task_id,
        
        #[Required, IntegerType, Min(1)]
        public readonly int $service_master_id,
        
        #[Required, IntegerType, Min(1)]
        public readonly int $quantity,
        
        #[Required, Min(0)]
        public readonly float $unit_price,
        
        #[Required, Min(0)]
        public readonly float $total_amount
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
            task_id: (int)($data['task_id'] ?? 0),
            service_master_id: (int)($data['service_master_id'] ?? 0),
            quantity: (int)($data['quantity'] ?? 0),
            unit_price: (float)($data['unit_price'] ?? 0),
            total_amount: (float)($data['total_amount'] ?? 0)
        );
    }
}
