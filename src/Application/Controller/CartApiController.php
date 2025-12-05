<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\CartService;
use App\Application\Service\MenuService;
use App\Infrastructure\SessionManager;

class CartApiController
{
    public function __construct(
        private AuthService $authService,
        private CartService $cartService,
        private MenuService $menuService,
        private SessionManager $session
    ) {
    }

    public function add(): void
    {
        header('Content-Type: application/json');
        if (!$this->session->get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Авторизуйтесь, чтобы добавлять в корзину']);
            return;
        }
        $id = intval($_POST['id'] ?? 0);
        $menuItem = $id > 0 ? $this->menuService->findItem($id) : null;
        if (!$menuItem) {
            echo json_encode(['success' => false, 'message' => 'Блюдо не найдено']);
            return;
        }
        if (!$menuItem->isToday) {
            echo json_encode(['success' => false, 'message' => 'Это блюдо недоступно в меню сегодня']);
            return;
        }
        if (!$this->cartService->addItem($id)) {
            echo json_encode(['success' => false, 'message' => 'Не удалось добавить блюдо в корзину']);
            return;
        }
        echo json_encode(['success' => true, 'message' => 'Блюдо добавлено в корзину']);
    }
}
