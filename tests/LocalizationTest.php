<?php
declare(strict_types=1);

session_save_path(sys_get_temp_dir());

require __DIR__ . '/../autoload.php';
require __DIR__ . '/../src/helpers.php';

use App\Application\Service\AdminMenuService;
use App\Application\Service\TranslateService;
use App\Domain\MenuItem;
use App\Infrastructure\Repository\MenuRepository;

class FakeTranslateService extends TranslateService
{
    public function __construct()
    {
    }

    public function translate(?string $text, string $locale): ?string
    {
        if (!is_string($text)) {
            return null;
        }
        $value = trim($text);
        if ($value === '') {
            return null;
        }
        return $value . ' [' . $locale . ']';
    }
}

function createAdminService(): array
{
    $db = new SQLite3(':memory:');
    $db->exec("CREATE TABLE menu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT,
        description TEXT,
        ingredients TEXT,
        price REAL,
        category TEXT,
        image_url TEXT,
        image_gallery TEXT,
        is_today INTEGER DEFAULT 0,
        use_manual_price INTEGER DEFAULT 0,
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
    $service = new AdminMenuService($repository, new FakeTranslateService());
    return [$service, $repository];
}

function assertEqual(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
    }
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function testCustomCategorySave(): void
{
    [$service, $repository] = createAdminService();
    $result = $service->save([
        'title' => 'Fusion Bowl',
        'description' => 'Slow cooked pork with basil',
        'ingredients' => 'pork, rice, basil',
        'price' => '7.50',
        'use_manual_price' => '1',
        'category_type' => 'custom',
        'category_custom' => 'Авторская категория',
        'existing_gallery' => '[]',
        'current_image' => '',
    ], []);
    if (!$result['success']) {
        throw new RuntimeException('Dish with custom category must be saved');
    }
    $items = $repository->findAll();
    assertEqual(1, count($items), 'Exactly one dish should be stored');
    $dish = $items[0];
    assertEqual('Fusion Bowl', $dish->nameOriginal, 'Original name stored');
    assertTrue(str_contains($dish->nameRu, '[ru]'), 'RU translation flag stored');
    assertTrue(str_contains($dish->nameLv, '[lv]'), 'LV translation flag stored');
    assertEqual('Авторская категория', $dish->categoryOriginal, 'Custom category stored');
    assertTrue(str_contains($dish->categoryRu, '[ru]'), 'Custom category RU translation created');
    assertTrue(str_contains($dish->categoryLv, '[lv]'), 'Custom category LV translation created');
    assertTrue(str_contains($dish->ingredientsRu, '[ru]'), 'Ingredients RU translation created');
    assertTrue(str_contains($dish->ingredientsLv, '[lv]'), 'Ingredients LV translation created');
}

function testStandardCategoryTranslations(): void
{
    [$service, $repository] = createAdminService();
    $result = $service->save([
        'title' => 'Tomato Soup',
        'description' => 'Creamy soup',
        'ingredients' => '',
        'price' => '0',
        'category_type' => 'soup',
        'existing_gallery' => '[]',
        'current_image' => '',
    ], []);
    if (!$result['success']) {
        throw new RuntimeException('Dish with standard category must be saved');
    }
    $items = $repository->findAll();
    $dish = $items[0];
    assertEqual(translate('category.soup', [], 'ru'), $dish->categoryOriginal, 'Standard category uses RU translation');
    assertEqual(translate('category.soup', [], 'ru'), $dish->categoryRu, 'RU category translation stored');
    assertEqual(translate('category.soup', [], 'lv'), $dish->categoryLv, 'LV category translation stored');
}

function testLocalizedAccessors(): void
{
    ensureSession();
    $_SESSION['locale'] = 'lv';
    $item = new MenuItem(
        id: 1,
        nameOriginal: 'Original',
        nameRu: 'Русское название',
        nameLv: 'Latviešu nosaukums',
        descriptionOriginal: 'Original description',
        descriptionRu: 'RU description',
        descriptionLv: 'LV apraksts',
        ingredientsOriginal: 'Original ingredients',
        ingredientsRu: 'RU ingredients',
        ingredientsLv: 'LV ingredients',
        price: 4.5,
        useManualPrice: false,
        categoryOriginal: 'Категория',
        categoryRu: 'Категория RU',
        categoryLv: 'Kategorija LV',
    );
    assertEqual('Latviešu nosaukums', $item->getNameAttribute(), 'LV locale returns LV name');
    assertEqual('LV apraksts', $item->getDescriptionAttribute(), 'LV locale returns LV description');
    assertEqual('Kategorija LV', $item->getCategoryAttribute(), 'LV locale returns LV category');
    assertEqual('LV ingredients', $item->getIngredientsAttribute(), 'LV locale returns LV ingredients');
    $_SESSION['locale'] = 'ru';
    assertEqual('Русское название', $item->getNameAttribute(), 'RU locale returns RU name');
    assertEqual('Категория RU', $item->getCategoryAttribute(), 'RU locale returns RU category');
    assertEqual('RU ingredients', $item->getIngredientsAttribute(), 'RU locale returns RU ingredients');
}

try {
    testCustomCategorySave();
    testStandardCategoryTranslations();
    testLocalizedAccessors();
    echo "Localization tests passed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Localization tests failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
