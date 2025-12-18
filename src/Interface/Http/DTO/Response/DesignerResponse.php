<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for designer data
 */
class DesignerResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $designer_id,
        public readonly string $name,
        public readonly string $contact_no,
        public readonly string $address,
        public readonly string $firm_name,
        public readonly string $profile_bg_color
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $designer
     * @return static
     */
    public static function fromEntity(object $designer): static
    {
        return new self(
            designer_id: $designer->getId(),
            name: $designer->getName(),
            contact_no: $designer->getContactNo(),
            address: $designer->getAddress(),
            firm_name: $designer->getFirmName(),
            profile_bg_color: $designer->getProfileBgColor()
        );
    }
}
