<?php

namespace App\Application\Controller\Admin;

use App\Application\Service\AuthService;
use App\Application\Service\OrderService;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;
use function setToast;

class OrderController
{
    public function __construct(
        private AuthService $authService,
        private OrderService $orderService,
        private ViewRenderer $view,
        private SessionManager $session
    ) {
    }

    private function requireAdmin(): void
    {
        if ($this->session->get('role') !== 'admin') {
            header('Location: /');
            exit;
        }
    }

    public function index(): string
    {
        $this->requireAdmin();
        $statusFilter = $_GET['status'] ?? '';
        $userSearch = trim($_GET['user'] ?? '');
        $includePending = ($statusFilter === 'pending');
        $orders = $this->orderService->adminOrders($statusFilter ?: null, $userSearch ?: null, $includePending);
        $summary = $this->orderService->summary();
        $pendingCount = $this->orderService->pendingCount();

        return $this->view->render('admin/orders', [
            'title' => 'Все заказы',
            'orders' => $orders,
            'summary' => $summary,
            'statusFilter' => $statusFilter,
            'userSearch' => $userSearch,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function updateStatus(): void
    {
        $this->requireAdmin();
        $orderId = intval($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($orderId > 0 && in_array($status, ['new','cooking','ready','delivered','cancelled'], true)) {
            $this->orderService->updateStatus($orderId, $status);
            setToast("Статус заказа #{$orderId} обновлён", 'info');
        } else {
            setToast('Выберите корректный статус', 'warning');
        }
        header('Location: /admin/orders');
        exit;
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $orderId = intval($_POST['id'] ?? 0);
        if ($orderId > 0) {
            $this->orderService->delete($orderId);
            setToast("Заказ #{$orderId} удалён", 'warning');
        }
        header('Location: /admin/orders');
        exit;
    }

    public function pendingFeed(): void
    {
        $this->requireAdmin();
        $orders = $this->orderService->pendingOrders();
        $payload = array_map(function ($order) {
            $items = array_map(function ($item) {
                return [
                    'title' => $item->title,
                    'quantity' => $item->quantity,
                ];
            }, $order->items ?? []);
            return [
                'id' => $order->id,
                'user' => $order->customerName ?? "Пользователь #{$order->userId}",
                'address' => $order->deliveryAddress,
                'total' => $order->totalPrice,
                'created_at' => $order->createdAt,
                'items' => $items,
                'link' => "/orders/view?id={$order->id}",
            ];
        }, $orders);
        header('Content-Type: application/json');
        echo json_encode([
            'count' => $this->orderService->pendingCount(),
            'orders' => $payload,
        ]);
        exit;
    }

    public function handlePending(): void
    {
        $this->requireAdmin();
        $orderId = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if ($orderId <= 0 || !in_array($action, ['accept','decline'], true)) {
            $this->json(['success' => false]);
        }
        if ($action === 'accept') {
            $this->orderService->updateStatus($orderId, 'new');
        } else {
            $this->orderService->updateStatus($orderId, 'cancelled');
        }
        $this->json(['success' => true]);
    }

    private function json(array $payload): void
    {
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
