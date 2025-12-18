<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Required;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;
use Framework\Validation\Attributes\StringType;

/**
 * Request DTO for creating a measurement
 */
class CreateMeasurementRequest extends RequestDTO
{
    public function __construct(
        #[Required, IntegerType, Min(1)]
        public readonly int $task_id,
        
        #[Required, StringType]
        public readonly string $location,
        
        #[Required, Min(0)]
        public readonly float $width,
        
        #[Required, Min(0)]
        public readonly float $height,
        
        #[Required, Min(0)]
        public readonly float $area,
        
        #[Required, StringType]
        public readonly string $unit,
        
        #[Required, IntegerType, Min(1)]
        public readonly int $quantity,
        
        #[Required, Min(0)]
        public readonly float $unit_price,
        
        #[Required, Min(0)]
        public readonly float $discount,
        
        #[Required, Min(0)]
        public readonly float $total_price,
        
        #[Required, StringType]
        public readonly string $notes
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
            location: $data['location'] ?? '',
            width: (float)($data['width'] ?? 0),
            height: (float)($data['height'] ?? 0),
            area: (float)($data['area'] ?? 0),
            unit: $data['unit'] ?? '',
            quantity: (int)($data['quantity'] ?? 0),
            unit_price: (float)($data['unit_price'] ?? 0),
            discount: (float)($data['discount'] ?? 0),
            total_price: (float)($data['total_price'] ?? 0),
            notes: $data['notes'] ?? ''
        );
    }
}
