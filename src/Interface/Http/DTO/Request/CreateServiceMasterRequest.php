<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Required;
use Framework\Validation\Attributes\StringType;
use Framework\Validation\Attributes\MaxLength;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;

/**
 * Request DTO for creating a service master
 */
class CreateServiceMasterRequest extends RequestDTO
{
    public function __construct(
        #[Required, StringType, MaxLength(255)]
        public readonly string $name,
        
        #[Required, IntegerType, Min(0)]
        public readonly int $default_rate
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
            name: $data['name'] ?? '',
            default_rate: (int)($data['default_rate'] ?? 0)
        );
    }
}
