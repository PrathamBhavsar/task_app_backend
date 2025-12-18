<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;
use Framework\Validation\Attributes\StringType;

/**
 * Request DTO for updating a measurement
 * 
 * All fields are optional for partial updates
 */
class UpdateMeasurementRequest extends RequestDTO
{
    public function __construct(
        #[IntegerType, Min(1)]
        public readonly ?int $task_id = null,
        
        #[StringType]
        public readonly ?string $location = null,
        
        #[Min(0)]
        public readonly ?float $width = null,
        
        #[Min(0)]
        public readonly ?float $height = null,
        
        #[Min(0)]
        public readonly ?float $area = null,
        
        #[StringType]
        public readonly ?string $unit = null,
        
        #[IntegerType, Min(1)]
        public readonly ?int $quantity = null,
        
        #[Min(0)]
        public readonly ?float $unit_price = null,
        
        #[Min(0)]
        public readonly ?float $discount = null,
        
        #[Min(0)]
        public readonly ?float $total_price = null,
        
        #[StringType]
        public readonly ?string $notes = null
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
            location: $data['location'] ?? null,
            width: isset($data['width']) ? (float)$data['width'] : null,
            height: isset($data['height']) ? (float)$data['height'] : null,
            area: isset($data['area']) ? (float)$data['area'] : null,
            unit: $data['unit'] ?? null,
            quantity: isset($data['quantity']) ? (int)$data['quantity'] : null,
            unit_price: isset($data['unit_price']) ? (float)$data['unit_price'] : null,
            discount: isset($data['discount']) ? (float)$data['discount'] : null,
            total_price: isset($data['total_price']) ? (float)$data['total_price'] : null,
            notes: $data['notes'] ?? null
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
