<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\CartService;
use App\Application\Service\OrderService;
use App\Domain\MenuRepositoryInterface;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;
use function setToast;

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
        $this->authService->requireLogin('Войдите или зарегистрируйтесь, чтобы просматривать заказы');
        $orders = $this->orderService->userOrders($this->session->get('user_id'));
        return $this->view->render('orders/history', [
            'title' => 'Doctor Gorilka — Мои заказы',
            'orders' => $orders,
        ]);
    }

    public function view(): string
    {
        $this->authService->requireLogin('Авторизуйтесь, чтобы просматривать заказ');
        $orderId = intval($_GET['id'] ?? 0);
        $order = $this->orderService->getOrder($orderId);
        if (!$order) {
            setToast('Заказ не найден или уже удалён', 'warning');
            $this->redirectBack();
        }
        $userId = $this->session->get('user_id');
        $isAdmin = $this->session->get('role') === 'admin';
        if (!$isAdmin && $order->userId !== $userId) {
            setToast('Заказ не найден или доступ запрещён', 'warning');
            $this->redirectBack();
        }

        if (!$isAdmin && isset($_GET['cancel']) && ($order->status === 'new' || $order->status === 'cooking')) {
            $this->orderService->updateStatus($orderId, 'cancelled');
            header('Location: /orders');
            exit;
        }

        return $this->view->render('orders/view', [
            'title' => 'Doctor Gorilka — Заказ #' . $orderId,
            'order' => $order,
            'isAdmin' => $isAdmin,
            'orderId' => $orderId,
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
        $this->authService->requireLogin('Авторизуйтесь, чтобы оформить заказ');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setToast('Выберите позиции для оформления заказа', 'warning');
            header('Location: /cart');
            exit;
        }

        $selectedIds = isset($_POST['items']) ? array_unique(array_map('strval', $_POST['items'])) : [];
        $selectedIds = array_values(array_filter($selectedIds, fn($value) => $value !== ''));
        $deliveryAddress = trim($_POST['delivery_address'] ?? '');

        if ($deliveryAddress === '') {
            $this->session->set('delivery_address_draft', '');
            setToast('Укажите адрес доставки', 'warning');
            header('Location: /cart');
            exit;
        }

        $this->session->set('delivery_address_draft', $deliveryAddress);

        $userId = $this->session->get('user_id');
        $result = $this->orderService->placeOrder($userId, $selectedIds, $deliveryAddress, $this->cartService, $this->menuRepository);
        if (!$result['success']) {
            setToast($result['message'] ?? 'Не удалось оформить заказ', 'danger');
            header('Location: /cart');
            exit;
        }

        $this->session->set('last_delivery_address', $result['delivery_address'] ?? $deliveryAddress);
        $this->session->unset('delivery_address_draft');

        $orderId = $result['order_id'];
        $items = $result['items'];
        $total = $result['total'];

        echo $this->view->render('orders/placed', [
            'title' => 'Doctor Gorilka — Заказ оформлен',
            'orderId' => $orderId,
            'items' => $items,
            'totalPrice' => $total,
            'orderAddress' => $result['delivery_address'] ?? $deliveryAddress,
        ]);
    }
}
