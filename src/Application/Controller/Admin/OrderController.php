<?php

namespace App\Application\Controller\Admin;

use App\Application\Service\AuthService;
use App\Application\Service\OrderService;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;
use function setToast;
use function translate;

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
            'title' => 'Doctor Gorilka — ' . translate('admin.orders.title'),
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
        if (!verify_csrf()) {
            setToast(translate('common.csrf_failed'), 'warning');
            header('Location: /admin/orders');
            exit;
        }
        $orderId = intval($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($orderId > 0 && in_array($status, ['new','cooking','ready','delivered','cancelled'], true)) {
            $changed = $this->orderService->updateStatus($orderId, $status, $this->session->get('user_id'));
            if ($changed) {
                setToast(translate('admin.orders.toast.status_updated', ['id' => $orderId]), 'info');
            } else {
                setToast(translate('admin.orders.toast.status_not_changed'), 'warning');
            }
        } else {
            setToast(translate('admin.orders.toast.status_invalid'), 'warning');
        }
        header('Location: /admin/orders');
        exit;
    }

    public function delete(): void
    {
        $this->requireAdmin();
        if (!verify_csrf()) {
            setToast(translate('common.csrf_failed'), 'warning');
            header('Location: /admin/orders');
            exit;
        }
        $orderId = intval($_POST['id'] ?? 0);
        if ($orderId > 0) {
            $this->orderService->delete($orderId);
            setToast(translate('admin.orders.toast.deleted', ['id' => $orderId]), 'warning');
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
            $createdLocal = \Carbon\Carbon::parse($order->createdAt, 'UTC')
                ->setTimezone(appTimezone())
                ->format(\DateTimeInterface::ATOM);
            return [
                'id' => $order->id,
                'code' => $order->orderCode,
                'user' => $order->customerName ?? translate('admin.pending.user_placeholder', ['id' => $order->userId]),
                'address' => $order->deliveryAddress,
                'total' => $order->totalPrice,
                'created_at' => $createdLocal,
                'comment' => $order->comment,
                'items' => $items,
                'link' => "/orders/view?code=" . urlencode($order->orderCode ?? ''),
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
        $success = $action === 'accept'
            ? $this->orderService->updateStatus($orderId, 'new', $this->session->get('user_id'))
            : $this->orderService->updateStatus($orderId, 'cancelled', $this->session->get('user_id'));
        $this->json(['success' => $success]);
    }

    public function show(): string
    {
        $this->requireAdmin();
        $orderId = intval($_GET['id'] ?? 0);
        $code = trim($_GET['code'] ?? '');
        $order = null;
        if ($orderId > 0) {
            $order = $this->orderService->getOrderWithHistory($orderId);
        }
        if (!$order && $code !== '') {
            $order = $this->orderService->getOrderByCodeWithHistory($code);
        }
        if (!$order) {
            setToast(translate('orders.errors.not_found'), 'warning');
            header('Location: /admin/orders');
            exit;
        }

        return $this->view->render('admin/order_show', [
            'title' => 'Doctor Gorilka — ' . translate('admin.orders.details.title', ['id' => $order->orderCode ?? $orderId]),
            'order' => $order,
        ]);
    }

    public function bulkStatus(): void
    {
        $this->requireAdmin();
        if (!verify_csrf()) {
            setToast(translate('common.csrf_failed'), 'warning');
            header('Location: /admin/orders');
            exit;
        }

        $orders = $_POST['orders'] ?? [];
        $status = trim($_POST['status'] ?? '');
        $allowedStatuses = ['new','cooking','ready','delivered'];
        if ($status === 'cancelled') {
            setToast(translate('admin.orders.toast.bulk_no_cancel'), 'warning');
            header('Location: /admin/orders');
            exit;
        }
        if (!in_array($status, $allowedStatuses, true)) {
            setToast(translate('admin.orders.toast.status_invalid'), 'warning');
            header('Location: /admin/orders');
            exit;
        }

        if (!is_array($orders) || empty($orders)) {
            setToast(translate('admin.orders.toast.bulk_empty'), 'warning');
            header('Location: /admin/orders');
            exit;
        }

        $adminId = $this->session->get('user_id');
        $orderIds = array_values(array_unique(array_map('intval', $orders)));
        $orderIds = array_filter($orderIds, static fn ($id) => $id > 0);
        $updated = 0;
        foreach ($orderIds as $id) {
            if ($this->orderService->updateStatus($id, $status, $adminId)) {
                $updated++;
            }
        }

        if ($updated > 0) {
            setToast(translate('admin.orders.toast.bulk_updated', ['count' => $updated]), 'info');
        } else {
            setToast(translate('admin.orders.toast.bulk_none'), 'warning');
        }
        header('Location: /admin/orders');
        exit;
    }

    private function json(array $payload): void
    {
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
