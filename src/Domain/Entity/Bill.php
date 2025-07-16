<?php

namespace Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "bills")]
class Bill implements \JsonSerializable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $bill_id;

    #[ORM\Column(type: "integer")]
    private int $task_id;

    #[ORM\Column(type: "datetime")]
    private \DateTime $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTime $due_date;

    #[ORM\Column(type: "float")]
    private float $subtotal;

    #[ORM\Column(type: "float")]
    private float $tax;

    #[ORM\Column(type: "float")]
    private float $total;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\Column(type: "string")]
    private ?string $additional_notes;

    public function __construct(
        int $taskId,
        \DateTime $dueDate,
        float $subtotal,
        float $total,
        float $tax,
        string $status,
        ?string $additionalNotes,

    ) {
        $this->task_id = $taskId;
        $this->due_date = $dueDate;
        $this->subtotal = $subtotal;
        $this->total = $total;
        $this->tax = $tax;
        $this->created_at = new \DateTime();
        $this->status = $status;
        $this->additional_notes = $additionalNotes;
    }

    public function jsonSerialize(): array
    {
        return [
            'bill_id' => $this->getId(),
            'task_id' => $this->getTaskId(),
            'due_date' => $this->getDueDate()->format('Y-m-d H:i:s'),
            'subtotal' => $this->getSubtotal(),
            'total' => $this->getTotal(),
            'tax' => $this->getTax(),
            'status' => $this->getStatus(),
            'additional_notes' => $this->getNotes(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    // Getters
    public function getId(): int
    {
        return $this->bill_id;
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function getDueDate(): \DateTime
    {
        return $this->due_date;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function getNotes(): ?string
    {
        return $this->additional_notes;
    }

    // Setters
    public function setTaskId(int $taskId): void
    {
        $this->task_id = $taskId;
    }

    public function setDueDate(DateTime $dueDate): void
    {
        $this->due_date = $dueDate;
    }

    public function setSubtotal(float $subtotal): void
    {
        $this->subtotal = $subtotal;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    public function setTax(float $tax): void
    {
        $this->tax = $tax;
    }

    public function setNotes(string $notes): void
    {
        $this->additional_notes = $notes;
    }
}
