<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\CartService;
use App\Application\Service\OrderService;
use App\Domain\MenuRepositoryInterface;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;
use function setToast;
use function translate;

class OrderController
{
    public function __construct(
        private AuthService $authService,
        private OrderService $orderService,
        private CartService $cartService,
        private MenuRepositoryInterface $menuRepository,
        private ViewRenderer $view,
        private SessionManager $session
    ) {
    }

    public function history(): string
    {
        $this->authService->requireLogin(translate('auth.require.orders'));
        $orders = $this->orderService->userOrders($this->session->get('user_id'));
        return $this->view->render('orders/history', [
            'title' => 'Doctor Gorilka — ' . translate('orders.history.title'),
            'orders' => $orders,
        ]);
    }

    public function view(): string
    {
        $this->authService->requireLogin(translate('auth.require.view_order'));
        $orderCode = trim($_GET['code'] ?? '');
        $orderId = intval($_GET['id'] ?? 0);
        $order = $orderCode !== '' ? $this->orderService->getOrderByCode($orderCode) : null;
        if (!$order && $orderId > 0) {
            $order = $this->orderService->getOrder($orderId);
        }
        if (!$order) {
            setToast(translate('orders.errors.not_found'), 'warning');
            $this->redirectBack();
        }
        $orderCode = $order->orderCode ?? $orderCode;
        $userId = $this->session->get('user_id');
        $isAdmin = $this->session->get('role') === 'admin';
        if (!$isAdmin && $order->userId !== $userId) {
            setToast(translate('orders.errors.forbidden'), 'warning');
            $this->redirectBack();
        }

        if (!$isAdmin && isset($_GET['cancel']) && ($order->status === 'new' || $order->status === 'cooking')) {
            $this->orderService->updateStatus($order->id, 'cancelled');
            header('Location: /orders');
            exit;
        }

        return $this->view->render('orders/view', [
            'title' => 'Doctor Gorilka — ' . translate('orders.view.title', ['id' => $orderCode ?: $order->id]),
            'order' => $order,
            'isAdmin' => $isAdmin,
            'orderId' => $order->id,
            'orderCode' => $orderCode,
        ]);
    }

    private function redirectBack(string $fallback = '/orders'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $target = $fallback;
        if ($referer !== '') {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            $currentHost = $_SERVER['HTTP_HOST'] ?? null;
            if ($refererHost && $currentHost && $refererHost === $currentHost) {
                $target = $referer;
            }
        }
        header('Location: ' . $target);
        exit;
    }

    public function place(): void
    {
        $this->authService->requireLogin(translate('auth.require.checkout'), '/login?next=/cart');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setToast(translate('orders.errors.select_items'), 'warning');
            header('Location: /cart');
            exit;
        }

        $selectedIds = isset($_POST['items']) ? array_unique(array_map('strval', $_POST['items'])) : [];
        $selectedIds = array_values(array_filter($selectedIds, fn($value) => $value !== ''));
        $deliveryAddress = trim($_POST['delivery_address'] ?? '');

        if ($deliveryAddress === '') {
            $this->session->set('delivery_address_draft', '');
            setToast(translate('orders.errors.address_required'), 'warning');
            header('Location: /cart');
            exit;
        }

        $this->session->set('delivery_address_draft', $deliveryAddress);

        $userId = $this->session->get('user_id');
        $result = $this->orderService->placeOrder($userId, $selectedIds, $deliveryAddress, $this->cartService, $this->menuRepository);
        if (!$result['success']) {
            setToast($result['message'] ?? translate('orders.errors.place_failed'), 'danger');
            header('Location: /cart');
            exit;
        }

        $this->session->set('last_delivery_address', $result['delivery_address'] ?? $deliveryAddress);
        $this->session->unset('delivery_address_draft');

        $orderId = $result['order_id'];
        $orderCode = $result['order_code'] ?? null;
        $items = $result['items'];
        $total = $result['total'];

        echo $this->view->render('orders/placed', [
            'title' => 'Doctor Gorilka — ' . translate('orders.placed.title'),
            'orderId' => $orderId,
            'orderCode' => $orderCode,
            'items' => $items,
            'totalPrice' => $total,
            'orderAddress' => $result['delivery_address'] ?? $deliveryAddress,
        ]);
    }
}
