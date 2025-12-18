<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Required;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;
use Framework\Validation\Attributes\StringType;
use Framework\Validation\Attributes\MaxLength;

/**
 * Request DTO for creating a timeline
 */
class CreateTimelineRequest extends RequestDTO
{
    public function __construct(
        #[Required, IntegerType, Min(1)]
        public readonly int $task_id,
        
        #[Required, StringType, MaxLength(50)]
        public readonly string $status,
        
        #[Required, IntegerType, Min(1)]
        public readonly int $user_id
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
            status: $data['status'] ?? '',
            user_id: (int)($data['user_id'] ?? 0)
        );
    }
}
