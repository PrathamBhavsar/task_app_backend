<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "service_master")]
class ServiceMaster implements \JsonSerializable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $service_master_id;

    #[ORM\Column(type: "string")]
    private string $name;

    #[ORM\Column(type: "integer")]
    private string $default_rate;

    public function __construct(
        string $name,
        int $defaultRate,
    ) {
        $this->name = $name;
        $this->default_rate = $defaultRate;
    }

    public function jsonSerialize(): array
    {
        return [
            'service_master_id' => $this->getId(),
            'name' => $this->getName(),
            'default_rate' => $this->getDefaultRate()
        ];
    }

    // Getters
    public function getId(): int
    {
        return $this->service_master_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDefaultRate(): int
    {
        return $this->default_rate;
    }

    // Setters
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setDefaultRate(int $defaultRate): void
    {
        $this->default_rate = $defaultRate;
    }
}
