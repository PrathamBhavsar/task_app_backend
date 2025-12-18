<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Email;
use Framework\Validation\Attributes\StringType;
use Framework\Validation\Attributes\MaxLength;

/**
 * Request DTO for updating a client
 * 
 * All fields are optional for partial updates
 */
class UpdateClientRequest extends RequestDTO
{
    public function __construct(
        #[StringType, MaxLength(255)]
        public readonly ?string $name = null,
        
        #[StringType, MaxLength(255)]
        public readonly ?string $contact_no = null,
        
        #[StringType]
        public readonly ?string $address = null,
        
        #[Email, MaxLength(255)]
        public readonly ?string $email = null
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
            contact_no: $data['contact_no'] ?? null,
            address: $data['address'] ?? null,
            email: $data['email'] ?? null
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
