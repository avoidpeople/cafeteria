<?php

namespace App\Application\Service;

use App\Domain\MenuItem;
use App\Domain\MenuRepositoryInterface;
use InvalidArgumentException;

class ComboService
{
    public const BASE_PRICE = 4.0;
    public const SOUP_EXTRA = 0.5;
    private const COMBO_CATEGORY = '_combo';
    private ?int $comboMenuId = null;

    public function __construct(private MenuRepositoryInterface $menuRepository)
    {
    }

    /**
     * @param array{main?: int|string, soup?: int|string} $selection
     */
    public function createCombo(array $selection): array
    {
        $mainId = isset($selection['main']) ? (int)$selection['main'] : 0;
        if ($mainId <= 0) {
            throw new InvalidArgumentException('Выберите горячее блюдо для комплекса');
        }

        $main = $this->menuRepository->findById($mainId);
        $this->assertAvailable($main, 'Горячее блюдо недоступно для комплексного обеда');

        $items = [$this->serializeItem($main, 'main')];

        $soupId = isset($selection['soup']) ? (int)$selection['soup'] : 0;
        $soup = null;
        if ($soupId > 0) {
            $soup = $this->menuRepository->findById($soupId);
            $this->assertAvailable($soup, 'Суп недоступен сегодня');
            $items[] = $this->serializeItem($soup, 'soup');
        }

        $price = $this->calculateComboPrice($main, $soup);
        $hasSoup = $soup !== null;
        $pricing = [
            'base' => $this->isUnique($main) ? $this->normalizePrice($main->price) : self::BASE_PRICE,
            'soup' => $soup ? ($this->isUnique($soup) ? $this->normalizePrice($soup->price) : self::SOUP_EXTRA) : 0.0,
        ];

        return [
            'id' => $selection['id'] ?? $this->generateId(),
            'title' => 'Комплексный обед',
            'price' => $price,
            'menu_id' => $this->comboMenuId(),
            'items' => $items,
            'has_soup' => $hasSoup,
            'pricing' => $pricing,
            'created_at' => time(),
        ];
    }

    private function serializeItem(MenuItem $item, string $type): array
    {
        $isUnique = $this->isUnique($item);
        return [
            'id' => $item->id,
            'title' => $item->title,
            'category' => $item->category ?? 'Без категории',
            'type' => $type,
            'image' => $item->primaryImage(),
            'description' => $item->description ?? null,
            'is_unique' => $isUnique,
            'price' => $isUnique ? $this->normalizePrice($item->price) : null,
        ];
    }

    public function isUnique(MenuItem $dish): bool
    {
        return $dish->isUnique();
    }

    public function calculateComboPrice(MenuItem $mainDish, ?MenuItem $soupDish = null): float
    {
        $price = $this->isUnique($mainDish) ? $this->normalizePrice($mainDish->price) : self::BASE_PRICE;
        if ($soupDish) {
            $price += $this->isUnique($soupDish)
                ? $this->normalizePrice($soupDish->price)
                : self::SOUP_EXTRA;
        }
        return round($price, 2);
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
            'title' => 'Комплексный обед',
            'description' => 'Системная позиция для комплексных обедов',
            'ingredients' => 'Автоматический комплект',
            'price' => self::BASE_PRICE + self::SOUP_EXTRA,
            'category' => self::COMBO_CATEGORY,
            'image_url' => null,
            'image_gallery' => [],
            'is_today' => 0,
        ]);
        $this->comboMenuId = $placeholder->id;
        return $this->comboMenuId;
    }
}
