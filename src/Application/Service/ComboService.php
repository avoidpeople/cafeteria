<?php

namespace App\Application\Service;

use App\Domain\MenuItem;
use App\Domain\MenuRepositoryInterface;
use InvalidArgumentException;
use function translate;

class ComboService
{
    public const BASE_PRICE = 4.0;
    private const COMBO_CATEGORY = '_combo';
    private ?int $comboMenuId = null;

    public function __construct(private MenuRepositoryInterface $menuRepository)
    {
    }

    /**
     * @param array{main?: int|string, garnish?: int|string, soup?: int|string, extras?: array<string,int>} $selection
     */
    public function createCombo(array $selection): array
    {
        $main = $this->loadRequiredDish((int)($selection['main'] ?? 0), 'main', translate('combo.errors.main_unavailable'));
        $garnish = $this->loadRequiredDish((int)($selection['garnish'] ?? 0), 'garnish', translate('combo.errors.garnish_unavailable'));
        $soup = $this->loadOptionalDish((int)($selection['soup'] ?? 0), 'soup', translate('combo.errors.soup_unavailable'));

        $extraSelections = [];
        if (!empty($selection['extras']) && is_array($selection['extras'])) {
            foreach ($selection['extras'] as $key => $id) {
                if (!is_string($key) || $key === '') {
                    continue;
                }
                $dish = $this->loadOptionalDish((int)$id, $key, translate('combo.errors.item_unavailable'), true);
                if ($dish) {
                    $extraSelections[$key] = $dish;
                }
            }
        }

        $items = [
            $this->serializeItem($main, 'main', $main->category ?? translate('combo.category.main'), true),
            $this->serializeItem($garnish, 'garnish', $garnish->category ?? translate('combo.category.garnish'), true),
        ];

        if ($soup) {
            $items[] = $this->serializeItem($soup, 'soup', $soup->category ?? translate('combo.category.soup'), false);
        }

        foreach ($extraSelections as $key => $dish) {
            $items[] = $this->serializeItem($dish, $key, $dish->category ?? translate('combo.category.extra'), false);
        }

        $total = self::BASE_PRICE;
        $pricingExtras = [];
        if ($soup && $this->hasComboPrice($soup)) {
            $price = $this->normalizePrice($soup->price);
            $pricingExtras['soup'] = $price;
            $total += $price;
        }
        foreach ($extraSelections as $key => $dish) {
            if (!$this->hasComboPrice($dish)) {
                continue;
            }
            $price = $this->normalizePrice($dish->price);
            $pricingExtras[$key] = $price;
            $total += $price;
        }

        $extraSelectionIds = [];
        foreach ($extraSelections as $key => $dish) {
            $extraSelectionIds[$key] = $dish->id;
        }

        return [
            'id' => $selection['id'] ?? $this->generateId(),
            'title' => translate('combo.title'),
            'price' => round($total, 2),
            'menu_id' => $this->comboMenuId(),
            'items' => $items,
            'selection' => [
                'main' => $main->id,
                'garnish' => $garnish->id,
                'soup' => $soup?->id,
                'extra' => $extraSelectionIds,
            ],
            'pricing' => [
                'base' => self::BASE_PRICE,
                'extras' => $pricingExtras,
            ],
            'created_at' => time(),
        ];
    }

    private function loadRequiredDish(int $id, string $expectedKey, string $errorMessage): MenuItem
    {
        $dish = $this->loadDish($id, $expectedKey, $errorMessage, false, true);
        if (!$dish) {
            throw new InvalidArgumentException($errorMessage);
        }
        return $dish;
    }

    private function loadOptionalDish(int $id, string $expectedKey, string $errorMessage, bool $strictKey = false): ?MenuItem
    {
        return $this->loadDish($id, $expectedKey, $errorMessage, true, $strictKey);
    }

    private function loadDish(int $id, string $expectedKey, string $message, bool $optional, bool $strictKey): ?MenuItem
    {
        if ($id <= 0) {
            if ($optional) {
                return null;
            }
            throw new InvalidArgumentException($message);
        }
        $dish = $this->menuRepository->findById($id);
        $this->assertAvailable($dish, $message);
        $actualKey = $this->resolveCategoryKey($dish);
        if ($strictKey && $actualKey !== $expectedKey) {
            throw new InvalidArgumentException($message);
        }
        if (!$strictKey && $expectedKey !== $actualKey) {
            throw new InvalidArgumentException($message);
        }
        return $dish;
    }

    private function serializeItem(MenuItem $item, string $key, string $label, bool $required): array
    {
        $price = (!$required && $this->hasComboPrice($item)) ? $this->normalizePrice($item->price) : 0.0;
        return [
            'id' => $item->id,
            'title' => $item->title,
            'category' => $label,
            'category_key' => $key,
            'image' => $item->primaryImage(),
            'description' => $item->description ?? null,
            'price' => $price,
            'required' => $required,
        ];
    }

    private function hasComboPrice(MenuItem $dish): bool
    {
        return $this->normalizePrice($dish->price) > 0;
    }

    private function assertAvailable(?MenuItem $item, string $message): void
    {
        if (!$item || !$item->isToday) {
            throw new InvalidArgumentException($message);
        }
    }

    private function normalizePrice(float $value): float
    {
        return round(max(0, $value), 2);
    }

    private function resolveCategoryKey(MenuItem $item): string
    {
        $role = $item->categoryRole ?? 'main';
        if ($role === 'custom') {
            $slug = $item->categoryKey ?: ('extra-' . $item->id);
            return 'extra:' . $slug;
        }
        return $role;
    }

    private function generateId(): string
    {
        return bin2hex(random_bytes(6));
    }

    private function comboMenuId(): int
    {
        if ($this->comboMenuId !== null) {
            return $this->comboMenuId;
        }

        $existing = $this->menuRepository->findAll('', self::COMBO_CATEGORY, false);
        if (!empty($existing[0])) {
            $this->comboMenuId = $existing[0]->id;
            return $this->comboMenuId;
        }

        $placeholder = $this->menuRepository->create([
            'title' => translate('combo.title'),
            'description' => translate('combo.system.description'),
            'ingredients' => translate('combo.system.ingredients'),
            'price' => self::BASE_PRICE,
            'category' => self::COMBO_CATEGORY,
            'image_url' => null,
            'image_gallery' => [],
            'is_today' => 0,
        ]);
        $this->comboMenuId = $placeholder->id;
        return $this->comboMenuId;
    }
}
