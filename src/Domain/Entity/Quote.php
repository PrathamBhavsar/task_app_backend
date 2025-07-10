<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "quotes")]
class Quote implements \JsonSerializable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $quote_id;

    #[ORM\Column(type: "float")]
    private float $subtotal;

    #[ORM\Column(type: "float")]
    private float $tax;

    #[ORM\Column(type: "float")]
    private float $total;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: "datetime")]
    private \DateTime $created_at;

    #[ORM\Column(type: "integer")]
    private string $task_id;

    public function __construct(
        float $subtotal,
        float $tax,
        float $total,
        ?string $notes,
        int $taskId,
    ) {
        $this->task_id = $taskId;
        $this->subtotal = $subtotal;
        $this->tax = $tax;
        $this->total = $total;
        $this->notes = $notes;
        $this->created_at = new \DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'quote_id' => $this->getId(),
            'task_id' => $this->getTaskId(),
            'subtotal' => $this->getSubtotal(),
            'tax' => $this->getTax(),
            'total' => $this->getTotal(),
            'notes' => $this->getNotes(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    // Getters
    public function getId(): int
    {
        return $this->quote_id;
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    // Setters
    public function setTaskId(int $taskId): void
    {
        $this->task_id = $taskId;
    }

    public function setSubtotal(float $subtotal): void
    {
        $this->subtotal = $subtotal;
    }

    public function setTax(float $tax): void
    {
        $this->tax = $tax;
    }

    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    public function setNotes(string $notes): void
    {
        $this->notes = $notes;
    }
}
