<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\NotificationService;
use App\Infrastructure\SessionManager;

class NotificationController
{
    public function __construct(
        private AuthService $authService,
        private NotificationService $notifications,
        private SessionManager $session
    ) {
    }

    public function latest(): void
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            $this->json([
                'authenticated' => false,
                'message' => 'Войдите, чтобы получать уведомления о заказах',
                'items' => [],
                'count' => 0,
            ]);
        }

        $list = $this->notifications->latest($userId);
        $items = [];
        $active = 0;
        foreach ($list as $notification) {
            $items[] = [
                'id' => $notification->id,
                'order_id' => $notification->orderId,
                'status' => $notification->status,
                'title' => "Заказ #{$notification->orderId}",
                'message' => $notification->message,
                'created_at' => $notification->createdAt,
                'amount' => $notification->amount,
                'link' => "/orders/view?id={$notification->orderId}",
            ];

            if (!in_array($notification->status, ['delivered', 'cancelled'], true)) {
                $active++;
            }
        }

        $this->json([
            'authenticated' => true,
            'items' => $items,
            'count' => $active,
        ]);
    }

    public function clear(): void
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            $this->json(['authenticated' => false, 'success' => false]);
        }
        $this->notifications->clear($userId);
        $this->json(['authenticated' => true, 'success' => true]);
    }

    private function json(array $payload): void
    {
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
