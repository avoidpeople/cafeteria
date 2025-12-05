<?php
// Путь к базе данных
$database = __DIR__ . '/../database/cafeteria.db';

// Создаем соединение с SQLite
try {
    $conn = new SQLite3($database);
} catch (Exception $e) {
    die("Ошибка подключения к базе: " . $e->getMessage());
}

// Обновляем структуру таблицы пользователей (имя/фамилия)
$userColumns = [];
$result = $conn->query("PRAGMA table_info(users)");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $userColumns[] = $row['name'];
}

$menuColumns = [];
$menuInfo = $conn->query("PRAGMA table_info(menu)");
while ($row = $menuInfo->fetchArray(SQLITE3_ASSOC)) {
    $menuColumns[] = $row['name'];
}

if (!in_array('first_name', $userColumns, true)) {
    $conn->exec("ALTER TABLE users ADD COLUMN first_name TEXT");
}

if (!in_array('last_name', $userColumns, true)) {
    $conn->exec("ALTER TABLE users ADD COLUMN last_name TEXT");
}

if (!in_array('phone', $userColumns, true)) {
    $conn->exec("ALTER TABLE users ADD COLUMN phone TEXT");
}

if (!in_array('image_gallery', $menuColumns, true)) {
    $conn->exec("ALTER TABLE menu ADD COLUMN image_gallery TEXT");
}

if (!in_array('is_today', $menuColumns, true)) {
    $conn->exec("ALTER TABLE menu ADD COLUMN is_today INTEGER DEFAULT 0");
}

$orderColumns = [];
$orderInfo = $conn->query("PRAGMA table_info(orders)");
while ($row = $orderInfo->fetchArray(SQLITE3_ASSOC)) {
    $orderColumns[] = $row['name'];
}

if (!in_array('delivery_address', $orderColumns, true)) {
    $conn->exec("ALTER TABLE orders ADD COLUMN delivery_address TEXT");
}

$orderTableSql = $conn->querySingle("SELECT sql FROM sqlite_master WHERE type='table' AND name='orders'");
if ($orderTableSql && strpos($orderTableSql, "'pending'") === false) {
    $conn->exec("ALTER TABLE orders RENAME TO orders_old");
    $conn->exec("CREATE TABLE orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        status TEXT CHECK(status IN ('pending','new','cooking','ready','delivered','cancelled')) DEFAULT 'pending',
        total_price REAL,
        delivery_address TEXT,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    $conn->exec("INSERT INTO orders (id, user_id, created_at, status, total_price, delivery_address)
                 SELECT id, user_id, created_at, status, total_price, delivery_address FROM orders_old");
    $conn->exec("DROP TABLE orders_old");
}

$conn->exec("CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    order_id INTEGER NOT NULL,
    status TEXT NOT NULL,
    message TEXT NOT NULL,
    amount REAL DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
)");
?>
