<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Required;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;
use Framework\Validation\Attributes\StringType;

/**
 * Request DTO for creating a task
 */
class CreateTaskRequest extends RequestDTO
{
    public function __construct(
        #[Required, StringType]
        public readonly string $deal_no,
        
        #[Required, StringType]
        public readonly string $name,
        
        #[Required, StringType]
        public readonly string $due_date,
        
        #[Required, StringType]
        public readonly string $priority,
        
        #[Required, StringType]
        public readonly string $remarks,
        
        #[Required, StringType]
        public readonly string $status,
        
        #[Required, IntegerType, Min(1)]
        public readonly int $client_id,
        
        #[Required, IntegerType, Min(1)]
        public readonly int $designer_id,
        
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
            deal_no: $data['deal_no'] ?? '',
            name: $data['name'] ?? '',
            due_date: $data['due_date'] ?? '',
            priority: $data['priority'] ?? '',
            remarks: $data['remarks'] ?? '',
            status: $data['status'] ?? '',
            client_id: (int)($data['client_id'] ?? 0),
            designer_id: (int)($data['designer_id'] ?? 0),
            agency_id: isset($data['agency_id']) ? (int)$data['agency_id'] : null
        );
    }
}
