<?php

namespace App\Domain;

class Order
{
    /** @param OrderItem[] $items */
    public function __construct(
        public int $id,
        public int $userId,
        public string $status,
        public float $totalPrice,
        public string $createdAt,
        public ?string $deliveryAddress,
        public ?string $orderCode = null,
        public array $items = [],
        public ?string $customerName = null,
        public ?string $customerPhone = null,
    ) {
    }
}
