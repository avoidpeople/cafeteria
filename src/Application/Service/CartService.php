<?php

namespace App\Application\Service;

use App\Domain\MenuItem;
use App\Domain\MenuRepositoryInterface;
use App\Infrastructure\SessionManager;

class CartService
{
    private const KEY = 'cart';

    public function __construct(
        private SessionManager $session,
        private MenuRepositoryInterface $menus
    ) {
        $this->session->set(self::KEY, $this->session->get(self::KEY, []));
    }

    private function getCart(): array
    {
        return $this->session->get(self::KEY, []);
    }

    private function saveCart(array $cart): void
    {
        $this->session->set(self::KEY, $cart);
    }

    public function addItem(int $id, int $quantity = 1): bool
    {
        if ($id <= 0) {
            return false;
        }
        $menuItem = $this->menus->findById($id);
        if (!$menuItem || !$menuItem->isToday) {
            return false;
        }
        $cart = $this->getCart();
        $cart[$id] = ($cart[$id] ?? 0) + max(1, $quantity);
        $this->saveCart($cart);
        return true;
    }

    public function decreaseItem(int $id): void
    {
        $cart = $this->getCart();
        if (isset($cart[$id])) {
            $cart[$id]--;
            if ($cart[$id] <= 0) {
                unset($cart[$id]);
            }
            $this->saveCart($cart);
        }
    }

    public function removeItem(int $id): void
    {
        $cart = $this->getCart();
        unset($cart[$id]);
        $this->saveCart($cart);
    }

    public function clear(): void
    {
        $this->saveCart([]);
    }

    public function detailedItems(): array
    {
        $cart = $this->getCart();
        if (!$cart) {
            return [[], 0];
        }
        $items = $this->menus->findByIds(array_keys($cart));
        $result = [];
        $total = 0;
        foreach ($items as $item) {
            $qty = $cart[$item->id] ?? 0;
            if ($qty <= 0) {
                continue;
            }
            $sum = $item->price * $qty;
            $result[] = [
                'item' => $item,
                'quantity' => $qty,
                'sum' => $sum,
            ];
            $total += $sum;
        }
        return [$result, $total];
    }

    public function hasItem(int $id): bool
    {
        $cart = $this->getCart();
        return isset($cart[$id]) && $cart[$id] > 0;
    }

    public function getQuantities(): array
    {
        return $this->getCart();
    }

    public function removeItems(array $ids): void
    {
        $cart = $this->getCart();
        foreach ($ids as $id) {
            unset($cart[$id]);
        }
        $this->saveCart($cart);
    }
}
