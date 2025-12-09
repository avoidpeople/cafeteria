<?php

namespace App\Application\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\CartService;
use App\Application\Service\ComboService;
use App\Infrastructure\SessionManager;
use function translate;

class CartApiController
{
    public function __construct(
        private AuthService $authService,
        private CartService $cartService,
        private ComboService $comboService,
        private SessionManager $session
    ) {
    }

    public function add(): void
    {
        header('Content-Type: application/json');
        if (!$this->session->get('user_id')) {
            echo json_encode(['success' => false, 'message' => translate('cart.api.login_required')]);
            return;
        }
        echo json_encode(['success' => false, 'message' => translate('cart.api.combo_only')]);
    }

    public function addCombo(): void
    {
        header('Content-Type: application/json');
        if (!$this->session->get('user_id')) {
            echo json_encode(['success' => false, 'message' => translate('cart.api.combo_login')]);
            return;
        }

        $mainId = intval($_POST['main_id'] ?? 0);
        $garnishId = intval($_POST['garnish_id'] ?? 0);
        $soupId = intval($_POST['soup_id'] ?? 0);
        $extrasInput = $_POST['extras'] ?? [];
        $extraSelections = [];
        if (is_array($extrasInput)) {
            foreach ($extrasInput as $key => $value) {
                if (!is_string($key) || $key === '') {
                    continue;
                }
                $intValue = (int)$value;
                if ($intValue > 0) {
                    $extraSelections[$key] = $intValue;
                }
            }
        }

        try {
            $combo = $this->comboService->createCombo([
                'main' => $mainId,
                'garnish' => $garnishId,
                'soup' => $soupId,
                'extras' => $extraSelections,
            ]);
            $this->cartService->addCombo($combo);
            echo json_encode(['success' => true, 'message' => translate('cart.api.combo_added')]);
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => translate('cart.api.combo_failed')]);
        }
    }
}
