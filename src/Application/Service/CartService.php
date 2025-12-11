<?php

namespace App\Application\Service;

use App\Domain\MenuRepositoryInterface;
use App\Infrastructure\SessionManager;
use function translate;

class CartService
{
    private const KEY = 'cart';

    public function __construct(
        private SessionManager $session,
        private MenuRepositoryInterface $menus
    ) {
        $this->session->set(self::KEY, $this->session->get(self::KEY, []));
        $userId = (int)$this->session->get('user_id', 0);
        if ($userId > 0) {
            $this->mergeUserCart($userId);
        }
    }

    private function getCart(): array
    {
        $cart = $this->session->get(self::KEY, []);
        if (!is_array($cart)) {
            $cart = [];
        }
        $items = $cart['items'] ?? null;
        $combos = $cart['combos'] ?? null;
        if ($items === null && $combos === null) {
            $items = $cart;
            $combos = [];
        }
        if (!is_array($items)) {
            $items = [];
        }
        if (!is_array($combos)) {
            $combos = [];
        }

        $items = array_filter(
            array_map(static fn ($qty) => (int)$qty, $items),
            static fn ($qty) => $qty > 0
        );

        $combos = array_filter($combos, static fn ($combo) => is_array($combo) && isset($combo['id'], $combo['menu_id']));

        return [
            'items' => $items,
            'combos' => $combos,
        ];
    }

    private function saveCart(array $cart): void
    {
        $cart['items'] = $cart['items'] ?? [];
        $cart['combos'] = $cart['combos'] ?? [];
        $this->session->set(self::KEY, $cart);
        $userId = (int)$this->session->get('user_id', 0);
        if ($userId > 0) {
            $this->session->set($this->userCartKey($userId), $cart);
        }
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
        $cart['items'][$id] = ($cart['items'][$id] ?? 0) + max(1, $quantity);
        $this->saveCart($cart);
        return true;
    }

    public function decreaseItem(int $id): void
    {
        $cart = $this->getCart();
        if (isset($cart['items'][$id])) {
            $cart['items'][$id]--;
            if ($cart['items'][$id] <= 0) {
                unset($cart['items'][$id]);
            }
            $this->saveCart($cart);
        }
    }

    public function removeItem(int $id): void
    {
        $cart = $this->getCart();
        unset($cart['items'][$id]);
        $this->saveCart($cart);
    }

    public function addCombo(array $combo): void
    {
        if (empty($combo['id']) || empty($combo['menu_id'])) {
            return;
        }
        $cart = $this->getCart();
        $cart['combos'][$combo['id']] = $combo;
        $this->saveCart($cart);
    }

    public function removeCombo(string $comboId): void
    {
        $cart = $this->getCart();
        unset($cart['combos'][$comboId]);
        $this->saveCart($cart);
    }

    public function clear(): void
    {
        $this->saveCart([
            'items' => [],
            'combos' => [],
        ]);
    }

    public function detailedItems(): array
    {
        $cart = $this->getCart();
        $itemsInCart = $cart['items'] ?? [];
        $combos = $cart['combos'] ?? [];
        if (empty($itemsInCart) && empty($combos)) {
            return [[], 0];
        }
        $items = $itemsInCart ? $this->menus->findByIds(array_keys($itemsInCart)) : [];
        $result = [];
        $total = 0;
        foreach ($items as $item) {
            $qty = $itemsInCart[$item->id] ?? 0;
            if ($qty <= 0) {
                continue;
            }
            $sum = $item->price * $qty;
            $result[] = [
                'type' => 'item',
                'id' => $item->id,
                'item' => $item,
                'quantity' => $qty,
                'sum' => $sum,
            ];
            $total += $sum;
        }

        foreach ($combos as $combo) {
            [$available, $missingItems] = $this->comboAvailability($combo);
            $price = (float)($combo['price'] ?? 0);
            $result[] = [
                'type' => 'combo',
                'id' => 'combo:' . $combo['id'],
                'combo' => $combo,
                'quantity' => 1,
                'sum' => $price,
                'available' => $available,
                'missing' => $missingItems,
            ];
            $total += $price;
        }
        return [$result, $total];
    }

    public function hasItem(int $id): bool
    {
        $cart = $this->getCart();
        return isset($cart['items'][$id]) && $cart['items'][$id] > 0;
    }

    public function getQuantities(): array
    {
        return $this->getCart();
    }

    public function mergeUserCart(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }
        $sessionCart = $this->getCart();
        $userCart = $this->session->get($this->userCartKey($userId), []);
        $merged = [
            'items' => [],
            'combos' => [],
        ];

        foreach ([$userCart, $sessionCart] as $cart) {
            foreach (($cart['items'] ?? []) as $id => $qty) {
                $intId = (int)$id;
                $intQty = (int)$qty;
                if ($intId > 0 && $intQty > 0) {
                    $merged['items'][$intId] = ($merged['items'][$intId] ?? 0) + $intQty;
                }
            }
            foreach (($cart['combos'] ?? []) as $comboId => $combo) {
                if (is_string($comboId) && is_array($combo) && isset($combo['id'], $combo['menu_id'])) {
                    $merged['combos'][$comboId] = $combo;
                }
            }
        }

        $this->saveCart($merged);
    }

    public function removeItems(array $ids): void
    {
        $cart = $this->getCart();
        foreach ($ids as $id) {
            if (is_string($id) && str_starts_with($id, 'combo:')) {
                $comboId = substr($id, 6);
                unset($cart['combos'][$comboId]);
                continue;
            }
            $intId = (int)$id;
            if ($intId > 0) {
                unset($cart['items'][$intId]);
            }
        }
        $this->saveCart($cart);
    }

    private function userCartKey(int $userId): string
    {
        return 'cart_user_' . $userId;
    }

    private function comboAvailability(array $combo): array
    {
        $missing = [];
        foreach ($combo['items'] ?? [] as $item) {
            $id = (int)($item['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $menuItem = $this->menus->findById($id);
            if (!$menuItem || !$menuItem->isToday) {
                $missing[] = [
                    'id' => $id,
                    'title' => $item['title'] ?? translate('common.dish'),
                ];
            }
        }
        return [empty($missing), $missing];
    }
}
