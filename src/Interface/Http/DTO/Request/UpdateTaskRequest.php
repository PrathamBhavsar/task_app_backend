<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;
use Framework\Validation\Attributes\StringType;

/**
 * Request DTO for updating a task
 * 
 * All fields are optional for partial updates
 */
class UpdateTaskRequest extends RequestDTO
{
    public function __construct(
        #[StringType]
        public readonly ?string $deal_no = null,
        
        #[StringType]
        public readonly ?string $name = null,
        
        #[StringType]
        public readonly ?string $start_date = null,
        
        #[StringType]
        public readonly ?string $due_date = null,
        
        #[StringType]
        public readonly ?string $priority = null,
        
        #[StringType]
        public readonly ?string $remarks = null,
        
        #[StringType]
        public readonly ?string $status = null,
        
        #[IntegerType, Min(1)]
        public readonly ?int $client_id = null,
        
        #[IntegerType, Min(1)]
        public readonly ?int $designer_id = null,
        
        #[IntegerType, Min(1)]
        public readonly ?int $agency_id = null
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
            deal_no: $data['deal_no'] ?? null,
            name: $data['name'] ?? null,
            start_date: $data['start_date'] ?? null,
            due_date: $data['due_date'] ?? null,
            priority: $data['priority'] ?? null,
            remarks: $data['remarks'] ?? null,
            status: $data['status'] ?? null,
            client_id: isset($data['client_id']) ? (int)$data['client_id'] : null,
            designer_id: isset($data['designer_id']) ? (int)$data['designer_id'] : null,
            agency_id: isset($data['agency_id']) ? (int)$data['agency_id'] : null
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
