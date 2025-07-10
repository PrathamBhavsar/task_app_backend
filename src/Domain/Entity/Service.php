<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "task_services")]
class Service implements \JsonSerializable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $task_service_id;

    #[ORM\Column(type: "integer")]
    private int $task_id;

    #[ORM\ManyToOne(targetEntity: ServiceMaster::class)]
    #[ORM\JoinColumn(name: "service_master_id", referencedColumnName: "service_master_id", nullable: false)]
    private ServiceMaster $service_master;

    #[ORM\Column(type: "integer")]
    private int $quantity;

    #[ORM\Column(type: "float")]
    private float $unit_price;

    #[ORM\Column(type: "float")]
    private float $total_amount;

    public function __construct(
        int $taskId,
        ServiceMaster $serviceMaster,
        int $quantity,
        float $unitPrice,
        float $totalAmount,
    ) {
        $this->task_id = $taskId;
        $this->service_master = $serviceMaster;
        $this->quantity = $quantity;
        $this->unit_price = $unitPrice;
        $this->total_amount = $totalAmount;
    }

    public function jsonSerialize(): array
    {
        return [
            'task_service_id' => $this->getId(),
            'task_id' => $this->getTaskId(),
            'service_master' => $this->getServiceMaster(),
            'quantity' => $this->getQuantity(),
            'unit_price' => $this->getUnitPrice(),
            'total_amount' => $this->getTotalAmount(),
        ];
    }

    // Getters
    public function getId(): int
    {
        return $this->task_service_id;
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function getServiceMaster(): ServiceMaster
    {
        return $this->service_master;
    }
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): float
    {
        return $this->unit_price;
    }

    public function getTotalAmount(): float
    {
        return $this->total_amount;
    }

    // Setters
    public function setTaskId(int $taskId): void
    {
        $this->task_id = $taskId;
    }

    public function setServiceMaster(ServiceMaster $serviceMaster): void
    {
        $this->service_master = $serviceMaster;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setUnitPrice(float $unitPrice): void
    {
        $this->unit_price = $unitPrice;
    }

    public function setTotalAmount(float $totalAmount): void
    {
        $this->total_amount = $totalAmount;
    }
}
