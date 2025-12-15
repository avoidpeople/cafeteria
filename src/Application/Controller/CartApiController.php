<?php

namespace App\Application\Controller;

use App\Application\Service\CartService;
use App\Application\Service\ComboService;
use function translate;
use function verify_csrf;

class CartApiController
{
    public function __construct(
        private CartService $cartService,
        private ComboService $comboService
    ) {
    }

    public function add(): void
    {
        if (!verify_csrf()) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => translate('common.csrf_failed')]);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => translate('cart.api.combo_only')]);
    }

    public function addCombo(): void
    {
        if (!verify_csrf()) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => translate('common.csrf_failed')]);
            return;
        }
        header('Content-Type: application/json');
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
