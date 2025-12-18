<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Request;

use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Required;
use Framework\Validation\Attributes\Email;
use Framework\Validation\Attributes\StringType;
use Framework\Validation\Attributes\MaxLength;

/**
 * Request DTO for creating a user
 */
class CreateUserRequest extends RequestDTO
{
    public function __construct(
        #[Required, StringType, MaxLength(255)]
        public readonly string $name,
        
        #[Required, Email, MaxLength(255)]
        public readonly string $email,
        
        #[Required, StringType]
        public readonly string $password,
        
        #[Required, StringType, MaxLength(255)]
        public readonly string $contact_no,
        
        #[Required, StringType]
        public readonly string $address,
        
        #[Required, StringType, MaxLength(50)]
        public readonly string $user_type,
        
        #[Required, StringType, MaxLength(50)]
        public readonly string $profile_bg_color
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
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
            contact_no: $data['contact_no'] ?? '',
            address: $data['address'] ?? '',
            user_type: $data['user_type'] ?? '',
            profile_bg_color: $data['profile_bg_color'] ?? ''
        );
    }
}
