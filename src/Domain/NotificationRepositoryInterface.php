<?php

namespace App\Domain;

interface NotificationRepositoryInterface
{
    public function add(int $userId, int $orderId, string $status, string $message, float $amount = 0): void;

    /** @return Notification[] */
    public function latestForUser(int $userId, ?int $limit = null): array;

    public function clearForUser(int $userId): void;
}
