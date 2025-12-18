<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Required;
use Framework\Validation\Attributes\Email;
use Framework\Validation\Attributes\StringType;
use Framework\Validation\Attributes\MaxLength;

/**
 * Request DTO for creating a client
 */
class CreateClientRequest extends RequestDTO
{
    public function __construct(
        #[Required, StringType, MaxLength(255)]
        public readonly string $name,
        
        #[Required, StringType, MaxLength(255)]
        public readonly string $contact_no,
        
        #[Required, StringType]
        public readonly string $address,
        
        #[Required, Email, MaxLength(255)]
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
