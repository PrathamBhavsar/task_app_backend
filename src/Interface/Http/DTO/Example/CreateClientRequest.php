<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Example;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Required;
use Framework\Validation\Attributes\Email;
use Framework\Validation\Attributes\StringType;
use Framework\Validation\Attributes\MaxLength;

/**
 * Example Request DTO for creating a client
 * 
 * This demonstrates the usage pattern for Request DTOs with validation attributes.
 */
class CreateClientRequest extends RequestDTO
{
    public function __construct(
        #[Required, StringType, MaxLength(255)]
        public readonly string $name,
        
        #[Required, StringType]
        public readonly string $contact_no,
        
        #[Required, StringType]
        public readonly string $address,
        
        #[Required, Email]
        public readonly string $email
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
            contact_no: $data['contact_no'] ?? '',
            address: $data['address'] ?? '',
            email: $data['email'] ?? ''
        );
    }
}
