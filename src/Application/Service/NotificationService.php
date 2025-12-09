<?php

namespace App\Application\Service;

use App\Domain\NotificationRepositoryInterface;
use function translate;

class NotificationService
{
    public function __construct(private NotificationRepositoryInterface $notifications)
    {
    }

    public function record(int $userId, int $orderId, string $status, float $amount, ?string $customMessage = null): void
    {
        $default = match ($status) {
            'pending' => 'notifications.status.pending',
            'new' => 'notifications.status.new',
            'cooking' => 'notifications.status.cooking',
            'ready' => 'notifications.status.ready',
            'delivered' => 'notifications.status.delivered',
            'cancelled' => 'notifications.status.cancelled',
            default => 'notifications.status.default',
        };
        $translation = translate($default);
        $message = $customMessage ?? ($translation === $default ? translate('notifications.status.default') : $translation);

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
