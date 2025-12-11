<?php

namespace App\Domain;

class OrderStatusHistory
{
    public function __construct(
        public int $orderId,
        public ?string $oldStatus,
        public string $newStatus,
        public ?int $changedBy,
        public string $changedAt,
        public ?int $id = null,
        public ?string $changedByName = null,
    ) {
    }
}
