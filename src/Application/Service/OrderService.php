<?php

namespace App\Application\Service;

use App\Domain\MenuRepositoryInterface;
use App\Domain\OrderRepositoryInterface;
use function translate;

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

    public function getOrderByCode(string $orderCode): ?\App\Domain\Order
    {
        return $this->orders->findByCode($orderCode);
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
            return ['success' => false, 'message' => translate('orders.errors.address_required')];
        }

        $cart = $cartService->getQuantities();
        $itemQuantities = $cart['items'] ?? [];
        $comboEntries = $cart['combos'] ?? [];

        $menuSelection = [];
        $comboSelection = [];
        foreach ($selectedIds as $value) {
            if (is_string($value) && str_starts_with($value, 'combo:')) {
                $comboSelection[] = substr($value, 6);
                continue;
            }
            $intValue = (int)$value;
            if ($intValue > 0) {
                $menuSelection[] = $intValue;
            }
        }

        $menuSelection = array_values(array_intersect($menuSelection, array_keys($itemQuantities)));
        $comboSelection = array_values(array_intersect($comboSelection, array_keys($comboEntries)));

        if (empty($menuSelection) && empty($comboSelection)) {
            return ['success' => false, 'message' => translate('orders.errors.cart_missing')];
        }

        $menuItems = $menuSelection ? $menuRepository->findByIds($menuSelection) : [];
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

        foreach ($comboSelection as $comboId) {
            $combo = $comboEntries[$comboId] ?? null;
            if (!$combo) {
                continue;
            }
            $menuId = (int)($combo['menu_id'] ?? 0);
            if ($menuId <= 0) {
                continue;
            }
            $price = (float)($combo['price'] ?? ComboService::BASE_PRICE);
            $items[] = [
                'menu_id' => $menuId,
                'quantity' => 1,
                'price' => $price,
                'title' => $combo['title'] ?? translate('combo.title'),
                'combo_details' => $combo,
                'type' => 'combo',
            ];
            $total += $price;
            $usedIds[] = 'combo:' . $comboId;
        }

        if (empty($items)) {
            return ['success' => false, 'message' => translate('orders.errors.items_unavailable')];
        }

        $order = $this->orders->create($userId, $deliveryAddress, $items, $total, 'pending');
        $this->notifications?->record($userId, $order->id, 'pending', $total);
        $cartService->removeItems($usedIds);

        return [
            'success' => true,
            'order_id' => $order->id,
            'order_code' => $order->orderCode,
            'items' => array_map(static fn ($item) => [
                'title' => $item['title'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'sum' => $item['price'] * $item['quantity'],
                'details' => $item['combo_details'] ?? null,
                'type' => $item['type'] ?? 'single',
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
