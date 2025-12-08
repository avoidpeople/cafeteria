<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\CartService;
use App\Application\Service\ComboService;
use App\Application\Service\MenuService;
use App\Infrastructure\SessionManager;

class CartApiController
{
    public function __construct(
        private AuthService $authService,
        private CartService $cartService,
        private MenuService $menuService,
        private ComboService $comboService,
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
        echo json_encode(['success' => false, 'message' => 'Отдельные блюда добавляются только через комплексный обед.']);
    }

    public function addCombo(): void
    {
        header('Content-Type: application/json');
        if (!$this->session->get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Авторизуйтесь, чтобы собирать комплексный обед']);
            return;
        }

        $mainId = intval($_POST['main_id'] ?? 0);
        $soupId = intval($_POST['soup_id'] ?? 0);

        try {
            $combo = $this->comboService->createCombo([
                'main' => $mainId,
                'soup' => $soupId,
            ]);
            $this->cartService->addCombo($combo);
            echo json_encode(['success' => true, 'message' => 'Комплексный обед добавлен в корзину']);
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Не удалось собрать комплексный обед']);
        }
    }
}
