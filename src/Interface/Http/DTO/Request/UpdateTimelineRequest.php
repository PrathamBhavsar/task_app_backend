<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;
use Framework\Validation\Attributes\StringType;
use Framework\Validation\Attributes\MaxLength;

/**
 * Request DTO for updating a timeline
 * 
 * All fields are optional for partial updates
 */
class UpdateTimelineRequest extends RequestDTO
{
    public function __construct(
        #[IntegerType, Min(1)]
        public readonly ?int $task_id = null,
        
        #[StringType, MaxLength(50)]
        public readonly ?string $status = null,
        
        #[IntegerType, Min(1)]
        public readonly ?int $user_id = null
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
            status: $data['status'] ?? null,
            user_id: isset($data['user_id']) ? (int)$data['user_id'] : null
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
