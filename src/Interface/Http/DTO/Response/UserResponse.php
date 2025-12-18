<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for user data
 */
class UserResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $user_id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $contact_no,
        public readonly string $address,
        public readonly string $user_type,
        public readonly string $profile_bg_color,
        public readonly string $created_at
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $user
     * @return static
     */
    public static function fromEntity(object $user): static
    {
        return new self(
            user_id: $user->getId(),
            name: $user->getName(),
            email: $user->getEmail(),
            contact_no: $user->getContactNo(),
            address: $user->getAddress(),
            user_type: $user->getUserType(),
            profile_bg_color: $user->getProfileBgColor(),
            created_at: $user->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }
}
