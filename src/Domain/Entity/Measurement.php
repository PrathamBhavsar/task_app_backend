<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "measurements")]
class Measurement implements \JsonSerializable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $measurement_id;

    #[ORM\Column(type: "integer")]
    private int $task_id;

    #[ORM\Column(type: "string")]
    private string $location;

    #[ORM\Column(type: "float")]
    private float $width;

    #[ORM\Column(type: "float")]
    private float $height;

    #[ORM\Column(type: "float")]
    private float $area;

    #[ORM\Column(type: "string")]
    private string $unit;

    #[ORM\Column(type: "string")]
    private string $notes;

    public function __construct(
        int $taskId,
        string $location,
        float $width,
        float $height,
        float $area,
        string $unit,
        string $notes,

    ) {
        $this->task_id = $taskId;
        $this->location = $location;
        $this->width = $width;
        $this->height = $height;
        $this->area = $area;
        $this->unit = $unit;
        $this->notes = $notes;
    }

    public function jsonSerialize(): array
    {
        return [
            'measurement_id' => $this->getId(),
            'location' => $this->getLocation(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'area' => $this->getArea(),
            'unit' => $this->getUnit(),
            'notes' => $this->getNotes(),
        ];
    }

    // Getters
    public function getId(): int
    {
        return $this->measurement_id;
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function getArea(): float
    {
        return $this->area;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    // Setters

    public function setTaskId(int $taskId): void
    {
        $this->task_id = $taskId;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
    }

    public function setArea(float $area): void
    {
        $this->area = $area;
    }

    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    public function setNotes(string $notes): void
    {
        $this->notes = $notes;
    }
}
