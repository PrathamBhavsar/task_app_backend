<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for service master data
 */
class ServiceMasterResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $service_master_id,
        public readonly string $name,
        public readonly int $default_rate
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $serviceMaster
     * @return static
     */
    public static function fromEntity(object $serviceMaster): static
    {
        return new self(
            service_master_id: $serviceMaster->getId(),
            name: $serviceMaster->getName(),
            default_rate: $serviceMaster->getDefaultRate()
        );
    }
}
