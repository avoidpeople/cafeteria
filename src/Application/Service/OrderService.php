<?php

namespace App\Application\Service;

use App\Domain\MenuRepositoryInterface;
use App\Domain\OrderRepositoryInterface;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private ?NotificationService $notifications = null
    )
    {
    }

    public function userOrders(int $userId): array
    {
        return $this->orders->findByUser($userId);
    }

    public function getOrder(int $orderId): ?\App\Domain\Order
    {
        return $this->orders->findById($orderId);
    }

    public function adminOrders(?string $status = null, ?string $search = null, bool $includePending = false): array
    {
        return $this->orders->findAll($status, $search, $includePending);
    }

    public function updateStatus(int $orderId, string $status): void
    {
        $order = $this->orders->findById($orderId);
        if (!$order) {
            return;
        }
        $this->orders->updateStatus($orderId, $status);
        $this->notifications?->record($order->userId, $orderId, $status, $order->totalPrice);
    }

    public function delete(int $orderId): void
    {
        $this->orders->delete($orderId);
    }

    public function placeOrder(
        int $userId,
        array $selectedIds,
        string $deliveryAddress,
        CartService $cartService,
        MenuRepositoryInterface $menuRepository
    ): array {
        $deliveryAddress = trim($deliveryAddress);
        if ($deliveryAddress === '') {
            return ['success' => false, 'message' => 'Укажите адрес доставки'];
        }

        $cart = $cartService->getQuantities();
        $validIds = array_values(array_intersect(array_map('intval', $selectedIds), array_keys($cart)));
        if (empty($validIds)) {
            return ['success' => false, 'message' => 'Выбранные блюда отсутствуют в корзине'];
        }

        $menuItems = $menuRepository->findByIds($validIds);
        $items = [];
        $total = 0;
        $usedIds = [];
        foreach ($menuItems as $menuItem) {
            $qty = $cart[$menuItem->id] ?? 0;
            if ($qty <= 0 || !$menuItem->isToday) {
                continue;
            }
            $sum = $menuItem->price * $qty;
            $items[] = [
                'menu_id' => $menuItem->id,
                'quantity' => $qty,
                'price' => $menuItem->price,
                'title' => $menuItem->title,
            ];
            $total += $sum;
            $usedIds[] = $menuItem->id;
        }

        if (empty($items)) {
            return ['success' => false, 'message' => 'Выбранные блюда недоступны в меню сегодня'];
        }

        $order = $this->orders->create($userId, $deliveryAddress, $items, $total, 'pending');
        $this->notifications?->record($userId, $order->id, 'pending', $total, 'Заказ оформлен и ожидает подтверждения');
        $cartService->removeItems($usedIds);

        return [
            'success' => true,
            'order_id' => $order->id,
            'items' => array_map(fn ($item) => [
                'title' => $item['title'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'sum' => $item['price'] * $item['quantity'],
            ], $items),
            'total' => $total,
            'delivery_address' => $deliveryAddress,
        ];
    }

    public function summary(): array
    {
        return $this->orders->summary();
    }

    public function userStats(int $userId): array
    {
        return $this->orders->userStats($userId);
    }

    public function pendingOrders(): array
    {
        return $this->orders->findPending();
    }

    public function pendingCount(): int
    {
        return $this->orders->countPending();
    }
}
