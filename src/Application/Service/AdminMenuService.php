<?php

namespace App\Application\Service;

use App\Domain\MenuItem;
use App\Domain\MenuRepositoryInterface;
use function translate;

class AdminMenuService
{
    public function __construct(
        private MenuRepositoryInterface $menuRepository,
        private TranslateService $translateService
    ) {
    }

    /** @return MenuItem[] */
    public function list(): array
    {
        return $this->menuRepository->findAll();
    }

    public function delete(int $id): void
    {
        $this->menuRepository->delete($id);
    }

    /** @return MenuItem[] */
    public function today(): array
    {
        return $this->menuRepository->findToday();
    }

    public function todayIds(): array
    {
        return $this->menuRepository->getTodayIds();
    }

    public function updateTodaySelection(array $ids): void
    {
        $this->menuRepository->setTodayMenu($ids);
    }

    public function save(array $data, array $files): array
    {
        $errors = [];
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
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
        ];

        if (!empty($data['id'])) {
            $this->menuRepository->update((int)$data['id'], $payload);
        } else {
            $this->menuRepository->create($payload);
        }

        return ['success' => true];
    }

    private function processGallery(array $files, string $existingGallery): array
    {
        $images = array_filter(array_map('trim', json_decode($existingGallery, true) ?? []));
        $errors = [];
        if (isset($files['image']['name']) && is_array($files['image']['name'])) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
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
        $selection = in_array($selection, ['hot', 'soup', 'custom'], true) ? $selection : 'hot';
        if ($selection === 'custom') {
            $value = trim($customValue);
            if ($value === '') {
                return ['errors' => [translate('admin.menu.errors.custom_category_required')]];
            }
            return [
                'category_original' => $value,
                'category_ru' => $this->translateText($value, 'ru'),
                'category_lv' => $this->translateText($value, 'lv'),
            ];
        }

        $ru = translate('category.' . $selection, [], 'ru');
        $lv = translate('category.' . $selection, [], 'lv');

        return [
            'category_original' => $ru,
            'category_ru' => $ru,
            'category_lv' => $lv,
        ];
    }
}
