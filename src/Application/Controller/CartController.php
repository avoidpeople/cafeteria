<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\CartService;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;

class CartController
{
    public function __construct(
        private AuthService $authService,
        private CartService $cartService,
        private ViewRenderer $view,
        private SessionManager $session
    ) {
    }

    public function index(): string
    {
        $this->authService->requireLogin('Войдите или зарегистрируйтесь, чтобы управлять корзиной');
        [$items, $total] = $this->cartService->detailedItems();
        $deliveryDraft = $this->session->get('delivery_address_draft') ?? $this->session->get('last_delivery_address', '');
        $this->session->unset('delivery_address_draft');

        return $this->view->render('cart/index', [
            'title' => 'Корзина',
            'cartItems' => $items,
            'totalPrice' => $total,
            'deliveryDraft' => $deliveryDraft,
        ]);
    }

    public function add(): void
    {
        $this->mutate(function (int $id) {
            $this->cartService->addItem($id);
        });
    }

    public function minus(): void
    {
        $this->mutate(function (int $id) {
            $this->cartService->decreaseItem($id);
        });
    }

    public function remove(): void
    {
        $this->mutate(function (int $id) {
            $this->cartService->removeItem($id);
        });
    }

    public function clear(): void
    {
        $this->authService->requireLogin('Войдите или зарегистрируйтесь, чтобы управлять корзиной');
        $this->cartService->clear();
        header('Location: /cart');
        exit;
    }

    private function mutate(callable $callback): void
    {
        $this->authService->requireLogin('Войдите или зарегистрируйтесь, чтобы управлять корзиной');
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            $callback($id);
        }
        header('Location: /cart');
        exit;
    }
}
