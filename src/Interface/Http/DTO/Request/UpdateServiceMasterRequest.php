<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\StringType;
use Framework\Validation\Attributes\MaxLength;
use Framework\Validation\Attributes\IntegerType;
use Framework\Validation\Attributes\Min;

/**
 * Request DTO for updating a service master
 * 
 * All fields are optional for partial updates
 */
class UpdateServiceMasterRequest extends RequestDTO
{
    public function __construct(
        #[StringType, MaxLength(255)]
        public readonly ?string $name = null,
        
        #[IntegerType, Min(0)]
        public readonly ?int $default_rate = null
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
            name: $data['name'] ?? null,
            default_rate: isset($data['default_rate']) ? (int)$data['default_rate'] : null
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
