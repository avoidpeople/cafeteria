<?php

namespace App\Application\Service;

use App\Domain\MenuItem;
use App\Domain\MenuRepositoryInterface;
use function translate;

class AdminMenuService
{
    private const COMBO_CATEGORY = '_combo';
    private const MAX_IMAGES = 5;
    private const MAX_SINGLE_IMAGE_BYTES = 5 * 1024 * 1024; // 5 MB
    private const MAX_TOTAL_IMAGE_BYTES = 12 * 1024 * 1024; // ~12 MB per request
    private const MAX_IMAGE_DIMENSION = 1920;
    private const MAX_TITLE_LENGTH = 180;
    private const MAX_DESCRIPTION_LENGTH = 2000;
    private const MAX_INGREDIENTS_LENGTH = 1000;
    private const MAX_ALLERGENS_LENGTH = 500;
    private const MAX_CATEGORY_LENGTH = 120;
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct(
        private MenuRepositoryInterface $menuRepository,
        private TranslateService $translateService,
        private ?string $imageStoragePath = null
    ) {
        $this->imageStoragePath = $imageStoragePath ?: __DIR__ . '/../../../public/assets/images';
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
        if ($item) {
            $this->deleteImagesByName($item->galleryImages());
        }
        $this->menuRepository->delete($id);
        $this->cleanupOrphanImages();
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
        $nameRu = trim($data['name_ru'] ?? '');
        $nameLv = trim($data['name_lv'] ?? '');
        $descRu = trim($data['description_ru'] ?? '');
        $descLv = trim($data['description_lv'] ?? '');
        $allergens = trim($data['allergens'] ?? '');
        $price = (float)($data['price'] ?? 0);
        $useManualPrice = !empty($data['use_manual_price']);
        $categoryType = $data['category_type'] ?? 'hot';
        $customCategory = trim($data['category_custom'] ?? '');
        $ingredients = trim($data['ingredients'] ?? '');
        if ($title === '') {
            $errors[] = translate('admin.menu.errors.title_required');
        }
        if (mb_strlen($title) > self::MAX_TITLE_LENGTH) {
            $errors[] = translate('admin.menu.errors.title_long', ['max' => (string) self::MAX_TITLE_LENGTH]);
        }
        if (mb_strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
            $errors[] = translate('admin.menu.errors.description_long', ['max' => (string) self::MAX_DESCRIPTION_LENGTH]);
        }
        if (mb_strlen($ingredients) > self::MAX_INGREDIENTS_LENGTH) {
            $errors[] = translate('admin.menu.errors.ingredients_long', ['max' => (string) self::MAX_INGREDIENTS_LENGTH]);
        }
        if ($allergens !== '' && mb_strlen($allergens) > self::MAX_ALLERGENS_LENGTH) {
            $errors[] = translate('admin.menu.errors.allergens_long', ['max' => (string) self::MAX_ALLERGENS_LENGTH]);
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
        if ($categoryType === 'custom' && mb_strlen($customCategory) > self::MAX_CATEGORY_LENGTH) {
            $errors[] = translate('admin.menu.errors.category_long', ['max' => (string) self::MAX_CATEGORY_LENGTH]);
        }
        foreach (['name_ru' => $nameRu, 'name_lv' => $nameLv] as $key => $value) {
            if ($value !== '' && mb_strlen($value) > self::MAX_TITLE_LENGTH) {
                $errors[] = translate('admin.menu.errors.name_long', ['field' => $key, 'max' => (string) self::MAX_TITLE_LENGTH]);
            }
        }
        foreach (['description_ru' => $descRu, 'description_lv' => $descLv] as $key => $value) {
            if ($value !== '' && mb_strlen($value) > self::MAX_DESCRIPTION_LENGTH) {
                $errors[] = translate('admin.menu.errors.description_long', ['max' => (string) self::MAX_DESCRIPTION_LENGTH]);
            }
        }

        $gallery = $this->processGallery($files, $data['existing_gallery'] ?? '');
        if (isset($gallery['errors'])) {
            $errors = array_merge($errors, $gallery['errors']);
        }
        $images = $gallery['images'] ?? [];
        $currentImage = $this->sanitizeImageName($data['current_image'] ?? null);
        $cover = $images[0] ?? $currentImage;
        $cover = $cover ? $this->sanitizeImageName($cover) : null;
        if ($cover && !in_array($cover, $images, true)) {
            array_unshift($images, $cover);
        }

        $categoryFields = $this->resolveCategoryFields($categoryType, $customCategory);
        if (isset($categoryFields['errors'])) {
            $errors = array_merge($errors, $categoryFields['errors']);
        }

        if ($errors) {
            $this->deleteImagesByName($gallery['uploaded'] ?? []);
            return ['success' => false, 'errors' => $errors];
        }

        $payload = [
            'title' => $this->truncate($title, self::MAX_TITLE_LENGTH),
            'description' => $this->truncate($description, self::MAX_DESCRIPTION_LENGTH),
            'ingredients' => $this->truncate($ingredients, self::MAX_INGREDIENTS_LENGTH),
            'price' => $price,
            'category' => $categoryFields['category_original'] ?? '',
            'image_url' => $images[0] ?? null,
            'image_gallery' => $images,
            'use_manual_price' => $useManualPrice,
            'name_original' => $title,
            'name_ru' => $nameRu !== '' ? $this->truncate($nameRu, self::MAX_TITLE_LENGTH) : $this->translateAndTruncate($title, 'ru', self::MAX_TITLE_LENGTH),
            'name_lv' => $nameLv !== '' ? $this->truncate($nameLv, self::MAX_TITLE_LENGTH) : $this->translateAndTruncate($title, 'lv', self::MAX_TITLE_LENGTH),
            'description_original' => $this->truncate($description, self::MAX_DESCRIPTION_LENGTH),
            'description_ru' => $descRu !== '' ? $this->truncate($descRu, self::MAX_DESCRIPTION_LENGTH) : $this->translateAndTruncate($description, 'ru', self::MAX_DESCRIPTION_LENGTH),
            'description_lv' => $descLv !== '' ? $this->truncate($descLv, self::MAX_DESCRIPTION_LENGTH) : $this->translateAndTruncate($description, 'lv', self::MAX_DESCRIPTION_LENGTH),
            'ingredients_original' => $this->truncate($ingredients, self::MAX_INGREDIENTS_LENGTH),
            'ingredients_ru' => $this->translateAndTruncate($ingredients, 'ru', self::MAX_INGREDIENTS_LENGTH),
            'ingredients_lv' => $this->translateAndTruncate($ingredients, 'lv', self::MAX_INGREDIENTS_LENGTH),
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
        $images = array_filter(array_map([$this, 'sanitizeImageName'], json_decode($existingGallery, true) ?? []));
        $errors = [];
        $uploaded = [];
        $this->ensureImageDirectory();
        $images = array_values(array_unique(array_filter($images)));
        if (isset($files['image']['name']) && is_array($files['image']['name'])) {
            $names = $files['image']['name'];
            $tmpNames = $files['image']['tmp_name'] ?? [];
            $errorsList = $files['image']['error'] ?? [];
            $sizes = $files['image']['size'] ?? [];
            $nonEmptyIndexes = array_keys(array_filter($names, static fn ($name) => (string)$name !== ''));
            if (count($nonEmptyIndexes) + count($images) > self::MAX_IMAGES) {
                $errors[] = translate('admin.menu.errors.images_limit', ['max' => (string) self::MAX_IMAGES]);
            }
            $totalBytes = 0;
            foreach ($files['image']['name'] as $index => $name) {
                if (empty($name)) {
                    continue;
                }
                $tmp = $tmpNames[$index] ?? '';
                $errorCode = $errorsList[$index] ?? UPLOAD_ERR_OK;
                $fileSize = (int)($sizes[$index] ?? 0);
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $isUploaded = $this->isUploadedFile($tmp);
                if (in_array($errorCode, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $fileSize > self::MAX_SINGLE_IMAGE_BYTES) {
                    $errors[] = translate('admin.menu.errors.image_size_single', ['name' => $name, 'max' => (string) $this->formatMegabytes(self::MAX_SINGLE_IMAGE_BYTES)]);
                    continue;
                }
                if ($errorCode !== UPLOAD_ERR_OK || !$isUploaded) {
                    $errors[] = translate('admin.menu.errors.invalid_image', ['name' => $name]);
                    continue;
                }
                $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
                if (!isset(self::ALLOWED_MIME_TYPES[$mime]) || !in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                    $errors[] = translate('admin.menu.errors.image_type', ['name' => $name]);
                    continue;
                }
                if ($fileSize <= 0) {
                    $fileSize = filesize($tmp);
                }
                $totalBytes += (int)$fileSize;
                if ($totalBytes > self::MAX_TOTAL_IMAGE_BYTES) {
                    $errors[] = translate('admin.menu.errors.image_size_total', ['max' => (string) $this->formatMegabytes(self::MAX_TOTAL_IMAGE_BYTES)]);
                    continue;
                }
                if (count($images) + count($uploaded) >= self::MAX_IMAGES) {
                    $errors[] = translate('admin.menu.errors.images_limit', ['max' => (string) self::MAX_IMAGES]);
                    break;
                }
                $filename = time() . '_' . bin2hex(random_bytes(4)) . '_' . $index . '.' . self::ALLOWED_MIME_TYPES[$mime];
                $target = rtrim($this->imageStoragePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
                if ($this->moveUploadedFile($tmp, $target)) {
                    $this->optimizeImage($target, $mime);
                    $uploaded[] = $filename;
                } else {
                    $errors[] = translate('admin.menu.errors.image_move_failed', ['name' => $name]);
                }
            }
        }
        if ($errors) {
            $this->deleteImagesByName($uploaded);
        } else {
            $images = array_merge($images, $uploaded);
        }
        $images = array_values(array_unique(array_slice(array_filter($images), 0, self::MAX_IMAGES)));
        return ['images' => $images, 'errors' => $errors, 'uploaded' => $uploaded];
    }

    private function translateText(?string $text, string $locale): ?string
    {
        $translated = $this->translateService->translate($text, $locale);
        if ($translated === null || $translated === '') {
            return $text !== '' ? $text : null;
        }
        return $translated;
    }

    private function translateAndTruncate(?string $text, string $locale, int $limit): ?string
    {
        return $this->truncate($this->translateText($text, $locale), $limit);
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

    private function truncate(?string $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }
        return mb_substr((string)$value, 0, $limit);
    }

    private function ensureImageDirectory(): void
    {
        if (!is_dir($this->imageStoragePath)) {
            @mkdir($this->imageStoragePath, 0775, true);
        }
    }

    private function sanitizeImageName(?string $name): ?string
    {
        $name = is_string($name) ? trim($name) : '';
        if ($name === '') {
            return null;
        }
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $name)) {
            return null;
        }
        return $name;
    }

    private function isUploadedFile(string $path): bool
    {
        if ($path === '') {
            return false;
        }
        return is_uploaded_file($path) || (PHP_SAPI === 'cli' && is_file($path));
    }

    private function moveUploadedFile(string $tmp, string $target): bool
    {
        if (is_uploaded_file($tmp)) {
            return move_uploaded_file($tmp, $target);
        }
        if (PHP_SAPI === 'cli' && is_file($tmp)) {
            return rename($tmp, $target) || copy($tmp, $target);
        }
        return false;
    }

    private function deleteImagesByName(array $names): void
    {
        foreach ($names as $name) {
            $safeName = $this->sanitizeImageName($name);
            if (!$safeName) {
                continue;
            }
            $path = rtrim($this->imageStoragePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;
            if (is_file($path)) {
                if (!@unlink($path)) {
                    error_log('[cleanup] Failed to delete image: ' . $safeName);
                }
            }
        }
    }

    private function cleanupOrphanImages(): void
    {
        if (!is_dir($this->imageStoragePath)) {
            return;
        }
        $used = array_flip($this->menuRepository->getAllImages());
        $files = glob(rtrim($this->imageStoragePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*');
        if ($files === false) {
            return;
        }
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            $name = basename($file);
            if (isset($used[$name])) {
                continue;
            }
            if (!preg_match('/^\\d{10}_[a-f0-9]{8}_[0-9]+\\.(jpe?g|png|gif|webp)$/i', $name)) {
                continue;
            }
            if (!@unlink($file)) {
                error_log('[cleanup] Unable to remove orphan image: ' . $name);
            }
        }
    }

    private function optimizeImage(string $path, string $mime): void
    {
        if (!extension_loaded('gd')) {
            return;
        }
        [$width, $height] = @getimagesize($path) ?: [null, null];
        if (!$width || !$height) {
            return;
        }
        if ($width <= self::MAX_IMAGE_DIMENSION && $height <= self::MAX_IMAGE_DIMENSION) {
            return;
        }
        $ratio = min(self::MAX_IMAGE_DIMENSION / $width, self::MAX_IMAGE_DIMENSION / $height);
        $newWidth = (int)floor($width * $ratio);
        $newHeight = (int)floor($height * $ratio);
        $imageCreate = match ($mime) {
            'image/png' => 'imagecreatefrompng',
            'image/gif' => 'imagecreatefromgif',
            'image/webp' => 'imagecreatefromwebp',
            default => 'imagecreatefromjpeg',
        };
        $imageSave = match ($mime) {
            'image/png' => fn ($img, $p) => imagepng($img, $p, 6),
            'image/gif' => fn ($img, $p) => imagegif($img, $p),
            'image/webp' => fn ($img, $p) => imagewebp($img, $p, 80),
            default => fn ($img, $p) => imagejpeg($img, $p, 82),
        };
        if (!function_exists($imageCreate)) {
            return;
        }
        $source = @$imageCreate($path);
        if (!$source) {
            return;
        }
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if (in_array($mime, ['image/png', 'image/gif'], true)) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $imageSave($resized, $path);
        imagedestroy($source);
        imagedestroy($resized);
    }

    private function formatMegabytes(int $bytes): string
    {
        return number_format($bytes / 1024 / 1024, 2, '.', '');
    }
}
