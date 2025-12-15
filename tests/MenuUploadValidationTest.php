<?php
declare(strict_types=1);

session_save_path(sys_get_temp_dir());

require __DIR__ . '/../autoload.php';
require __DIR__ . '/../src/helpers.php';

use App\Application\Service\AdminMenuService;
use App\Application\Service\TranslateService;
use App\Infrastructure\Repository\MenuRepository;

function createMenuTestStack(string $storagePath): array
{
    $db = new SQLite3(':memory:');
    $db->exec("CREATE TABLE menu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        ingredients TEXT,
        allergens TEXT,
        price REAL,
        use_manual_price INTEGER DEFAULT 0,
        image_url TEXT,
        image_gallery TEXT,
        category TEXT,
        is_today INTEGER DEFAULT 0,
        name_original TEXT,
        name_ru TEXT,
        name_lv TEXT,
        description_original TEXT,
        description_ru TEXT,
        description_lv TEXT,
        category_original TEXT,
        category_ru TEXT,
        category_lv TEXT,
        category_role TEXT,
        category_key TEXT,
        ingredients_original TEXT,
        ingredients_ru TEXT,
        ingredients_lv TEXT
    )");

    $repository = new MenuRepository($db);
    $translate = new class extends TranslateService {
        public function translate(?string $text, string $locale): ?string
        {
            return $text;
        }
    };
    $service = new AdminMenuService($repository, $translate, $storagePath);

    ensureSession();
    $_SESSION['locale'] = 'ru';

    return [$service, $repository];
}

function assertMenu(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function buildFakeImage(string $dir, string $name, int $sizeBytes = 2048): string
{
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HwAFAgH+5Tz2UwAAAABJRU5ErkJggg==');
    $padding = max(0, $sizeBytes - strlen($png));
    file_put_contents($path, $png . str_repeat('A', $padding));
    return $path;
}

function buildUploadArray(array $paths): array
{
    return [
        'name' => array_map('basename', $paths),
        'type' => array_fill(0, count($paths), 'image/png'),
        'tmp_name' => $paths,
        'error' => array_fill(0, count($paths), UPLOAD_ERR_OK),
        'size' => array_map('filesize', $paths),
    ];
}

function cleanupDir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $files = glob($dir . '/*');
    if (is_array($files)) {
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
    @rmdir($dir);
}

function testTooManyImagesRejected(): void
{
    $storage = sys_get_temp_dir() . '/gorilka_uploads_' . uniqid();
    [$service] = createMenuTestStack($storage);
    $maxImages = (new ReflectionClass(AdminMenuService::class))->getConstant('MAX_IMAGES');
    $paths = [];
    for ($i = 0; $i < $maxImages + 1; $i++) {
        $paths[] = buildFakeImage($storage, "test{$i}.png");
    }
    $result = $service->save([
        'title' => 'Dish',
        'description' => 'Desc',
        'ingredients' => 'Water',
        'use_manual_price' => 1,
        'price' => 10,
        'category_type' => 'main',
        'existing_gallery' => json_encode([]),
    ], ['image' => buildUploadArray($paths)]);

    assertMenu($result['success'] === false, 'Too many images must be rejected');
    $hasLimitError = array_reduce($result['errors'], fn ($carry, $err) => $carry || str_contains($err, (string)$maxImages), false);
    assertMenu($hasLimitError, 'Error should mention max image count');
    cleanupDir($storage);
}

function testRejectsOversizeFile(): void
{
    $storage = sys_get_temp_dir() . '/gorilka_uploads_' . uniqid();
    [$service] = createMenuTestStack($storage);
    $maxBytes = (new ReflectionClass(AdminMenuService::class))->getConstant('MAX_SINGLE_IMAGE_BYTES');
    $oversizePath = buildFakeImage($storage, 'big.png', $maxBytes + 1024);
    $result = $service->save([
        'title' => 'Dish',
        'description' => 'Desc',
        'ingredients' => 'Water',
        'use_manual_price' => 1,
        'price' => 10,
        'category_type' => 'main',
        'existing_gallery' => json_encode([]),
    ], ['image' => buildUploadArray([$oversizePath])]);

    assertMenu($result['success'] === false, 'Oversized file must be rejected');
    $expected = translate('admin.menu.errors.image_size_single', [
        'name' => 'big.png',
        'max' => number_format($maxBytes / 1024 / 1024, 2, '.', ''),
    ]);
    $hasSizeError = in_array($expected, $result['errors'] ?? [], true);
    assertMenu($hasSizeError, 'Oversized error message must be present');
    cleanupDir($storage);
}

function testDeleteRemovesImages(): void
{
    $storage = sys_get_temp_dir() . '/gorilka_uploads_' . uniqid();
    [$service, $repository] = createMenuTestStack($storage);
    $fileName = time() . '_cleanup_0.png';
    $filePath = buildFakeImage($storage, $fileName, 4096);

    $item = $repository->create([
        'title' => 'Cleanup dish',
        'description' => 'Desc',
        'ingredients' => 'Water',
        'price' => 5.0,
        'category' => 'hot',
        'image_url' => $fileName,
        'image_gallery' => [$fileName],
        'name_original' => 'Cleanup dish',
        'description_original' => 'Desc',
        'ingredients_original' => 'Water',
        'category_original' => 'hot',
        'category_role' => 'main',
    ]);

    assertMenu(file_exists($filePath), 'Test image must exist before deletion');
    $deleted = $service->delete($item->id);
    assertMenu($deleted === true, 'Delete call should succeed');
    assertMenu(!file_exists($filePath), 'Image file must be removed from storage');
    assertMenu($repository->findById($item->id) === null, 'Menu item should be removed from DB');
    cleanupDir($storage);
}

try {
    testTooManyImagesRejected();
    testRejectsOversizeFile();
    testDeleteRemovesImages();
    echo "Menu upload validation tests passed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Menu upload validation tests failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
