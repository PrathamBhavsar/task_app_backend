<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;
use Framework\Validation\Attributes\StringType;

/**
 * Request DTO for updating a quote
 * 
 * All fields are optional for partial updates
 */
class UpdateQuoteRequest extends RequestDTO
{
    public function __construct(
        #[IntegerType, Min(1)]
        public readonly ?int $task_id = null,
        
        #[Min(0)]
        public readonly ?float $subtotal = null,
        
        #[Min(0)]
        public readonly ?float $tax = null,
        
        #[Min(0)]
        public readonly ?float $total = null,
        
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
            subtotal: isset($data['subtotal']) ? (float)$data['subtotal'] : null,
            tax: isset($data['tax']) ? (float)$data['tax'] : null,
            total: isset($data['total']) ? (float)$data['total'] : null,
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
        $fields = [];
        
        if ($this->task_id !== null) {
            $fields['task_id'] = $this->task_id;
        }
        if ($this->subtotal !== null) {
            $fields['subtotal'] = $this->subtotal;
        }
        if ($this->tax !== null) {
            $fields['tax'] = $this->tax;
        }
        if ($this->total !== null) {
            $fields['total'] = $this->total;
        }
        if ($this->notes !== null) {
            $fields['notes'] = $this->notes;
        }
        
        return $fields;
    }
}
