<?php

namespace App\Domain;

class OrderItem
{
    public function __construct(
        public int $menuId,
        public string $title,
        public float $price,
        public int $quantity,
        public ?string $imageUrl = null,
        public ?array $comboDetails = null,
    ) {
    }

    public function sum(): float
    {
        return $this->price * $this->quantity;
    }

    public function isCombo(): bool
    {
        return !empty($this->comboDetails);
    }
}
