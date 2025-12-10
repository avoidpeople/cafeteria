<?php

namespace App\Domain;

class Notification
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $orderId,
        public ?string $orderCode,
        public string $status,
        public string $message,
        public float $amount,
        public string $createdAt,
    ) {
    }
}
