<?php
declare(strict_types=1);

session_save_path(sys_get_temp_dir());

require __DIR__ . '/../autoload.php';
require __DIR__ . '/../src/helpers.php';

use App\Infrastructure\Repository\OrderRepository;

function createOrderRepository(): array
{
    $db = new SQLite3(':memory:');
    $db->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        first_name TEXT,
        last_name TEXT,
        phone TEXT
    )");
    $db->exec("CREATE TABLE menu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        price REAL NOT NULL DEFAULT 0,
        image_url TEXT,
        name_original TEXT,
        name_ru TEXT,
        name_lv TEXT
    )");
    $db->exec("CREATE TABLE orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_code TEXT UNIQUE,
        user_id INTEGER NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        status TEXT CHECK(status IN ('pending','new','cooking','ready','delivered','cancelled')) DEFAULT 'pending',
        total_price REAL,
        delivery_address TEXT
    )");
    $db->exec("CREATE UNIQUE INDEX idx_orders_order_code ON orders(order_code)");
    $db->exec("CREATE TABLE order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        menu_id INTEGER NOT NULL,
        quantity INTEGER NOT NULL,
        combo_details TEXT
    )");
    $db->exec("INSERT INTO users (username) VALUES ('tester')");
    $db->exec("INSERT INTO menu (title, price) VALUES ('Dish', 2.5)");

    return [new OrderRepository($db), $db];
}

function createOrder(OrderRepository $repository, int $userId = 1): \App\Domain\Order
{
    return $repository->create($userId, 'Test address', [
        ['menu_id' => 1, 'quantity' => 1, 'price' => 2.5, 'title' => 'Dish'],
    ], 2.5, 'pending');
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function testOrderCodeGenerated(): void
{
    [$repo] = createOrderRepository();
    $order = createOrder($repo);
    $code = $order->orderCode;
    assertTrue(is_string($code) && preg_match('/^CAF-[A-HJ-NP-Z2-9]{4,6}$/', $code) === 1, 'Order code must match format CAF-XXXX');
}

function testOrderCodeUnique(): void
{
    [$repo] = createOrderRepository();
    $first = createOrder($repo);
    $second = createOrder($repo);
    assertTrue($first->orderCode !== $second->orderCode, 'Order codes must be unique');
}

function testOrderCodeStableAfterUpdate(): void
{
    [$repo] = createOrderRepository();
    $order = createOrder($repo);
    $original = $order->orderCode;
    $repo->updateStatus($order->id, 'new');
    $refetched = $repo->findById($order->id);
    assertTrue($refetched !== null && $refetched->orderCode === $original, 'Order code must not change after updates');
}

function testFindByCode(): void
{
    [$repo] = createOrderRepository();
    $order = createOrder($repo);
    $found = $repo->findByCode($order->orderCode);
    assertTrue($found !== null && $found->id === $order->id, 'findByCode must return the correct order');
}

try {
    testOrderCodeGenerated();
    testOrderCodeUnique();
    testOrderCodeStableAfterUpdate();
    testFindByCode();
    echo "Order code tests passed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Order code tests failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
