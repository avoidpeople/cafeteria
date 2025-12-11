<?php

namespace App\Application\Controller;

use App\Application\Service\CartService;
use App\Infrastructure\SessionManager;
use App\Infrastructure\ViewRenderer;
use function translate;

class CartController
{
    public function __construct(
        private CartService $cartService,
        private ViewRenderer $view,
        private SessionManager $session
    ) {
    }

    public function index(): string
    {
        [$items, $total] = $this->cartService->detailedItems();
        $deliveryDraft = $this->session->get('delivery_address_draft') ?? $this->session->get('last_delivery_address', '');
        $this->session->unset('delivery_address_draft');

        return $this->view->render('cart/index', [
            'title' => 'Doctor Gorilka — ' . translate('cart.title'),
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

    public function removeCombo(): void
    {
        $comboId = trim($_GET['combo'] ?? '');
        if ($comboId !== '') {
            $this->cartService->removeCombo($comboId);
        }
        header('Location: /cart');
        exit;
    }

    public function clear(): void
    {
        $this->cartService->clear();
        header('Location: /cart');
        exit;
    }

    private function mutate(callable $callback): void
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            $callback($id);
        }
        header('Location: /cart');
        exit;
    }
}
