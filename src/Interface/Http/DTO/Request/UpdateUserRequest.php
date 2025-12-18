<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Email;
use Framework\Validation\Attributes\StringType;
use Framework\Validation\Attributes\MaxLength;

/**
 * Request DTO for updating a user
 * 
 * All fields are optional for partial updates
 */
class UpdateUserRequest extends RequestDTO
{
    public function __construct(
        #[StringType, MaxLength(255)]
        public readonly ?string $name = null,
        
        #[Email, MaxLength(255)]
        public readonly ?string $email = null,
        
        #[StringType]
        public readonly ?string $password = null,
        
        #[StringType, MaxLength(255)]
        public readonly ?string $contact_no = null,
        
        #[StringType]
        public readonly ?string $address = null,
        
        #[StringType, MaxLength(50)]
        public readonly ?string $user_type = null,
        
        #[StringType, MaxLength(50)]
        public readonly ?string $profile_bg_color = null
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
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            contact_no: $data['contact_no'] ?? null,
            address: $data['address'] ?? null,
            user_type: $data['user_type'] ?? null,
            profile_bg_color: $data['profile_bg_color'] ?? null
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
