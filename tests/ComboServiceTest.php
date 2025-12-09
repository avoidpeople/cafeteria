<?php
declare(strict_types=1);

session_save_path(sys_get_temp_dir());

require __DIR__ . '/../autoload.php';
require __DIR__ . '/../src/helpers.php';

use App\Application\Service\ComboService;
use App\Infrastructure\Repository\MenuRepository;

function createComboService(): array
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
    $service = new ComboService($repository);
    return [$service, $repository];
}

function seedDish(MenuRepository $repository, array $overrides): int
{
    $defaults = [
        'title' => 'Dish',
        'description' => 'Description',
        'ingredients' => '',
        'price' => 0,
        'category' => 'Категория',
        'image_url' => null,
        'image_gallery' => [],
        'is_today' => 1,
        'use_manual_price' => 0,
        'name_original' => 'Dish',
        'name_ru' => 'Dish RU',
        'name_lv' => 'Dish LV',
        'description_original' => 'Description',
        'description_ru' => 'Description RU',
        'description_lv' => 'Description LV',
        'category_original' => 'Категория',
        'category_ru' => 'Категория',
        'category_lv' => 'Kategorija',
        'category_role' => 'main',
        'category_key' => 'main',
        'ingredients_original' => '',
        'ingredients_ru' => '',
        'ingredients_lv' => '',
    ];
    $payload = array_merge($defaults, $overrides);
    return $repository->create($payload)->id;
}

function assertComboEqual(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
    }
}

function assertComboTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function testComboRequiresMandatoryDishes(): void
{
    [$service, $repository] = createComboService();
    $mainId = seedDish($repository, [
        'title' => 'Main',
        'category_role' => 'main',
        'category_key' => 'main',
    ]);
    $garnishId = seedDish($repository, [
        'title' => 'Side',
        'category_role' => 'garnish',
        'category_key' => 'garnish',
    ]);

    try {
        $service->createCombo(['main' => $mainId, 'garnish' => 0]);
        throw new RuntimeException('Combo must require garnish selection');
    } catch (\InvalidArgumentException $e) {
        $message = $e->getMessage();
        $expected = translate('combo.errors.garnish_unavailable');
        assertComboTrue(strpos($message, $expected) !== false || strpos($message, 'гарнир') !== false, 'Garnish validation message expected');
    }

    $combo = $service->createCombo(['main' => $mainId, 'garnish' => $garnishId]);
    assertComboEqual(ComboService::BASE_PRICE, $combo['price'], 'Base combo price is unchanged without extras');
}

function testComboPricingWithExtras(): void
{
    [$service, $repository] = createComboService();
    $mainId = seedDish($repository, [
        'title' => 'Hot',
        'category_role' => 'main',
        'category_key' => 'main',
    ]);
    $garnishId = seedDish($repository, [
        'title' => 'Garnish',
        'category_role' => 'garnish',
        'category_key' => 'garnish',
    ]);
    $soupId = seedDish($repository, [
        'title' => 'Soup',
        'price' => 1.5,
        'category' => 'Суп',
        'category_original' => 'Суп',
        'category_ru' => 'Суп',
        'category_lv' => 'Zupa',
        'category_role' => 'soup',
        'category_key' => 'soup',
    ]);
    $extraId = seedDish($repository, [
        'title' => 'Dessert',
        'price' => 2.25,
        'category' => 'Dessert',
        'category_original' => 'Dessert',
        'category_ru' => 'Десерт',
        'category_lv' => 'Deserts',
        'category_role' => 'custom',
        'category_key' => 'dessert',
    ]);

    $combo = $service->createCombo([
        'main' => $mainId,
        'garnish' => $garnishId,
        'soup' => $soupId,
        'extras' => ['extra:dessert' => $extraId],
    ]);
    $expectedPrice = ComboService::BASE_PRICE + 1.5 + 2.25;
    if (abs($combo['price'] - $expectedPrice) > 0.001) {
        throw new RuntimeException('Combo price mismatch. Expected ' . $expectedPrice . ' got ' . $combo['price']);
    }
    assertComboEqual($extraId, $combo['selection']['extra']['extra:dessert'] ?? null, 'Extra selection must keep category key');
    assertComboEqual(1.5, $combo['pricing']['extras']['soup'] ?? null, 'Soup surcharge stored');
    assertComboEqual(2.25, $combo['pricing']['extras']['extra:dessert'] ?? null, 'Extra surcharge stored');
}

try {
    testComboRequiresMandatoryDishes();
    testComboPricingWithExtras();
    echo "Combo service tests passed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Combo service tests failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
