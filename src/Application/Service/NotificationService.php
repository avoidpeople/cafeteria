<?php

namespace App\Application\Service;

use App\Domain\NotificationRepositoryInterface;

class NotificationService
{
    public function __construct(private NotificationRepositoryInterface $notifications)
    {
    }

    public function record(int $userId, int $orderId, string $status, float $amount, ?string $customMessage = null): void
    {
        $message = $customMessage ?? match ($status) {
            'pending' => 'Заказ оформлен, ожидает подтверждения',
            'new' => 'Заказ принят в работу',
            'cooking' => 'Заказ готовится',
            'ready' => 'Заказ готов к выдаче',
            'delivered' => 'Заказ выдан',
            'cancelled' => 'Заказ отменён',
            default => 'Статус заказа обновлён',
        };

        $this->notifications->add($userId, $orderId, $status, $message, $amount);
    }

    public function latest(int $userId, ?int $limit = null): array
    {
        return $this->notifications->latestForUser($userId, $limit);
    }

    public function clear(int $userId): void
    {
        $this->notifications->clearForUser($userId);
    }
}
