<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Required;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;
use Framework\Validation\Attributes\StringType;

/**
 * Request DTO for creating a quote
 */
class CreateQuoteRequest extends RequestDTO
{
    public function __construct(
        #[Required, IntegerType, Min(1)]
        public readonly int $task_id,
        
        #[Required, Min(0)]
        public readonly float $subtotal,
        
        #[Required, Min(0)]
        public readonly float $tax,
        
        #[Required, Min(0)]
        public readonly float $total,
        
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
            task_id: (int)($data['task_id'] ?? 0),
            subtotal: (float)($data['subtotal'] ?? 0),
            tax: (float)($data['tax'] ?? 0),
            total: (float)($data['total'] ?? 0),
            notes: $data['notes'] ?? null
        );
    }
}
