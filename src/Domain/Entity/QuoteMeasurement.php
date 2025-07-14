<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "quote_measurements")]
class QuoteMeasurement implements \JsonSerializable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $quote_measurement_id;

    #[ORM\Column(type: "integer")]
    private int $quote_id;

    #[ORM\Column(type: "integer")]
    private int $measurement_id;

    #[ORM\Column(type: "integer")]
    private int $quantity;

    #[ORM\Column(type: "float")]
    private float $unit_price;

    #[ORM\Column(type: "float")]
    private float $total_price;

    #[ORM\Column(type: "float")]
    private float $discount;

    public function __construct(
        int $quoteId,
        int $measurementId,
        int $quantity,
        float $unitPrice,
        float $totalPrice,
        float $discount,
    ) {
        $this->quote_id = $quoteId;
        $this->measurement_id = $measurementId;
        $this->quantity = $quantity;
        $this->unit_price = $unitPrice;
        $this->total_price = $totalPrice;
        $this->discount = $discount;
    }

    public function jsonSerialize(): array
    {
        return [
            'quote_measurement_id' => $this->getId(),
            'quote_id' => $this->getQuoteId(),
            'measurement_id' => $this->getMeasurementId(),
            'quantity' => $this->getQuantity(),
            'unit_price' => $this->getUnitPrice(),
            'total_price' => $this->getTotalPrice(),
            'discount' => $this->getDiscount(),
        ];
    }

    // Getters
    public function getId(): int
    {
        return $this->quote_measurement_id;
    }

    public function getQuoteId(): int
    {
        return $this->quote_id;
    }

    public function getMeasurementId(): int
    {
        return $this->measurement_id;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getUnitPrice(): float
    {
        return $this->unit_price;
    }

    public function getTotalPrice(): string
    {
        return $this->total_price;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    // Setters
    public function setQuoteId(int $quoteId): void
    {
        $this->quote_id = $quoteId;
    }

    public function setMeasurementId(int $measurementId): void
    {
        $this->measurement_id = $measurementId;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setUnitPrice(float $unitPrice): void
    {
        $this->unit_price = $unitPrice;
    }

    public function setTotalPrice(float $totalPrice): void
    {
        $this->total_price = $totalPrice;
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }
}
