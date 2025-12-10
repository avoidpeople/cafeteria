<?php

namespace App\Application\Service;

use App\Domain\MenuItem;
use App\Domain\MenuRepositoryInterface;
use function translate;

class AdminMenuService
{
    private const COMBO_CATEGORY = '_combo';

    public function __construct(
        private MenuRepositoryInterface $menuRepository,
        private TranslateService $translateService
    ) {
    }

    /** @return MenuItem[] */
    public function list(): array
    {
        $items = $this->menuRepository->findAll();
        usort($items, [$this, 'compareMenuItems']);
        return $items;
    }

    public function delete(int $id): bool
    {
        $item = $this->menuRepository->findById($id);
        if ($item && self::isSystemCombo($item)) {
            return false;
        }
        $this->menuRepository->delete($id);
        return true;
    }

    /** @return MenuItem[] */
    public function today(): array
    {
        $items = $this->menuRepository->findToday();
        $items = array_values(array_filter($items, static fn (MenuItem $item) => !self::isSystemCombo($item)));
        usort($items, [$this, 'compareMenuItems']);
        return $items;
    }

    public function todayIds(): array
    {
        return $this->menuRepository->getTodayIds();
    }

    public function updateTodaySelection(array $ids): void
    {
        $filtered = [];
        foreach ($ids as $id) {
            $intId = (int)$id;
            if ($intId <= 0) {
                continue;
            }
            $item = $this->menuRepository->findById($intId);
            if ($item && self::isSystemCombo($item)) {
                continue;
            }
            $filtered[] = $intId;
        }
        $this->menuRepository->setTodayMenu($filtered);
    }

    public function save(array $data, array $files): array
    {
        $id = !empty($data['id']) ? (int)$data['id'] : null;
        if ($id) {
            $existing = $this->menuRepository->findById($id);
            if ($existing && self::isSystemCombo($existing)) {
                return ['success' => false, 'errors' => [translate('admin.menu.errors.system_locked')]];
            }
        }
        $errors = [];
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $allergens = trim($data['allergens'] ?? '');
        $price = (float)($data['price'] ?? 0);
        $useManualPrice = !empty($data['use_manual_price']);
        $categoryType = $data['category_type'] ?? 'hot';
        $customCategory = trim($data['category_custom'] ?? '');
        if ($title === '') {
            $errors[] = translate('admin.menu.errors.title_required');
        }
        if ($useManualPrice && $price <= 0) {
            $errors[] = translate('admin.menu.errors.manual_price');
        }
        if (!$useManualPrice) {
            $price = 0.0;
        }
        if ($categoryType === 'custom' && $customCategory === '') {
            $errors[] = translate('admin.menu.errors.custom_category_required');
        }

        $gallery = $this->processGallery($files, $data['existing_gallery'] ?? '');
        if (isset($gallery['errors'])) {
            $errors = array_merge($errors, $gallery['errors']);
        }
        $images = $gallery['images'] ?? [];
        $cover = $images[0] ?? ($data['current_image'] ?? null);
        if ($cover && !in_array($cover, $images, true)) {
            array_unshift($images, $cover);
        }

        $categoryFields = $this->resolveCategoryFields($categoryType, $customCategory);
        if (isset($categoryFields['errors'])) {
            $errors = array_merge($errors, $categoryFields['errors']);
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $ingredients = trim($data['ingredients'] ?? '');

        $payload = [
            'title' => $title,
            'description' => $description,
            'ingredients' => $ingredients,
            'price' => $price,
            'category' => $categoryFields['category_original'] ?? '',
            'image_url' => $images[0] ?? null,
            'image_gallery' => $images,
            'use_manual_price' => $useManualPrice,
            'name_original' => $title,
            'name_ru' => $this->translateText($title, 'ru'),
            'name_lv' => $this->translateText($title, 'lv'),
            'description_original' => $description,
            'description_ru' => $this->translateText($description, 'ru'),
            'description_lv' => $this->translateText($description, 'lv'),
            'ingredients_original' => $ingredients,
            'ingredients_ru' => $this->translateText($ingredients, 'ru'),
            'ingredients_lv' => $this->translateText($ingredients, 'lv'),
            'category_original' => $categoryFields['category_original'] ?? null,
            'category_ru' => $categoryFields['category_ru'] ?? null,
            'category_lv' => $categoryFields['category_lv'] ?? null,
            'category_role' => $categoryFields['category_role'] ?? 'main',
            'category_key' => $categoryFields['category_key'] ?? null,
            'allergens' => $allergens !== '' ? $allergens : null,
        ];

        if (!empty($data['id'])) {
            $this->menuRepository->update((int)$data['id'], $payload);
        } else {
            $this->menuRepository->create($payload);
        }

        return ['success' => true];
    }

    private static function isSystemCombo(MenuItem $item): bool
    {
        return ($item->category ?? '') === self::COMBO_CATEGORY;
    }

    private function processGallery(array $files, string $existingGallery): array
    {
        $images = array_filter(array_map('trim', json_decode($existingGallery, true) ?? []));
        $errors = [];
        if (isset($files['image']['name']) && is_array($files['image']['name'])) {
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];
            foreach ($files['image']['name'] as $index => $name) {
                if (empty($name)) {
                    continue;
                }
                $tmp = $files['image']['tmp_name'][$index] ?? '';
                if (!is_uploaded_file($tmp)) {
                    continue;
                }
                $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
                if (!isset($allowed[$mime])) {
                    $errors[] = translate('admin.menu.errors.invalid_image', ['name' => $name]);
                    continue;
                }
                $filename = time() . '_' . bin2hex(random_bytes(4)) . '_' . $index . '.' . $allowed[$mime];
                $target = __DIR__ . '/../../../public/assets/images/' . $filename;
                if (move_uploaded_file($tmp, $target)) {
                    $images[] = $filename;
                }
            }
        }
        return ['images' => array_values(array_unique(array_filter($images))), 'errors' => $errors];
    }

    private function translateText(?string $text, string $locale): ?string
    {
        $translated = $this->translateService->translate($text, $locale);
        if ($translated === null || $translated === '') {
            return $text !== '' ? $text : null;
        }
        return $translated;
    }

    private function resolveCategoryFields(string $selection, string $customValue): array
    {
        $selection = in_array($selection, ['main', 'garnish', 'soup', 'custom'], true) ? $selection : 'main';
        if ($selection === 'custom') {
            $value = trim($customValue);
            if ($value === '') {
                return ['errors' => [translate('admin.menu.errors.custom_category_required')]];
            }
            $slug = $this->slugifyCategory($value);
            return [
                'category_original' => $value,
                'category_ru' => $this->translateText($value, 'ru'),
                'category_lv' => $this->translateText($value, 'lv'),
                'category_role' => 'custom',
                'category_key' => $slug,
            ];
        }

        $keyMap = [
            'main' => 'hot',
            'garnish' => 'garnish',
            'soup' => 'soup',
        ];
        $categoryKey = $keyMap[$selection] ?? 'hot';
        $ru = translate('category.' . $categoryKey, [], 'ru');
        $lv = translate('category.' . $categoryKey, [], 'lv');

        return [
            'category_original' => $ru,
            'category_ru' => $ru,
            'category_lv' => $lv,
            'category_role' => $selection,
            'category_key' => $selection,
        ];
    }

    private function slugifyCategory(string $value): string
    {
        $value = strtolower(trim($value));
        if (function_exists('transliterator_transliterate')) {
            $value = transliterator_transliterate('Any-Latin; Latin-ASCII', $value);
        }
        $value = preg_replace('/[^a-z0-9]+/u', '-', $value);
        $value = trim((string)$value, '-');
        return $value ?: bin2hex(random_bytes(3));
    }

    private function categoryWeight(MenuItem $item): int
    {
        $order = [
            'main' => 1,
            'garnish' => 2,
            'soup' => 3,
        ];
        return $order[$item->categoryRole ?? 'main'] ?? 99;
    }

    private function compareMenuItems(MenuItem $a, MenuItem $b): int
    {
        $aSystem = self::isSystemCombo($a);
        $bSystem = self::isSystemCombo($b);
        if ($aSystem !== $bSystem) {
            return $aSystem ? -1 : 1;
        }
        $cmpWeight = $this->categoryWeight($a) <=> $this->categoryWeight($b);
        if ($cmpWeight !== 0) {
            return $cmpWeight;
        }
        return strnatcasecmp($a->title, $b->title);
    }
}
