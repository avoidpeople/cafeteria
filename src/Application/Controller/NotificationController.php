<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\NotificationService;
use App\Infrastructure\SessionManager;
use function translate;
use function verify_csrf;

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
                'message' => translate('notifications.login_prompt'),
                'items' => [],
                'count' => 0,
            ]);
            return;
        }

        $list = $this->notifications->latest($userId);
        $items = [];
        $active = 0;
        foreach ($list as $notification) {
            $messageKey = 'notifications.status.' . $notification->status;
            $message = translate($messageKey);
            if ($message === $messageKey) {
                $message = $notification->message ?: translate('notifications.status.default');
            }
            $createdAt = \Carbon\Carbon::parse($notification->createdAt, 'UTC')
                ->setTimezone(appTimezone())
                ->toIso8601String();
            $items[] = [
                'id' => $notification->id,
                'status' => $notification->status,
                'title' => translate('notifications.order_title', ['id' => $notification->orderCode ?? $notification->orderId]),
                'message' => $message,
                'created_at' => $createdAt,
                'amount' => $notification->amount,
                'link' => "/orders/view?code=" . urlencode($notification->orderCode ?? (string)$notification->orderId),
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
        if (!verify_csrf()) {
            $this->json(['authenticated' => false, 'success' => false, 'message' => translate('common.csrf_failed')]);
        }
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
