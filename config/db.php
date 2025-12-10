<?php
$database = __DIR__ . '/../database/doctor_gorilka.db';

try {
    $conn = new SQLite3($database);
} catch (Exception $e) {
    die("Ошибка подключения к базе: " . $e->getMessage());
}

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

if (!in_array('ingredients', $menuColumns, true)) {
    $conn->exec("ALTER TABLE menu ADD COLUMN ingredients TEXT");
}

if (!in_array('allergens', $menuColumns, true)) {
    $conn->exec("ALTER TABLE menu ADD COLUMN allergens TEXT");
}

if (!in_array('use_manual_price', $menuColumns, true)) {
    $conn->exec("ALTER TABLE menu ADD COLUMN use_manual_price INTEGER DEFAULT 0");
}

$localizedColumns = [
    'name_original' => 'TEXT',
    'name_ru' => 'TEXT',
    'name_lv' => 'TEXT',
    'description_original' => 'TEXT',
    'description_ru' => 'TEXT',
    'description_lv' => 'TEXT',
    'category_original' => 'TEXT',
    'category_ru' => 'TEXT',
    'category_lv' => 'TEXT',
    'category_role' => "TEXT DEFAULT 'main'",
    'category_key' => 'TEXT',
    'ingredients_original' => 'TEXT',
    'ingredients_ru' => 'TEXT',
    'ingredients_lv' => 'TEXT',
];

foreach ($localizedColumns as $column => $type) {
    if (!in_array($column, $menuColumns, true)) {
        $conn->exec("ALTER TABLE menu ADD COLUMN {$column} {$type}");
        switch ($column) {
            case 'name_original':
                $conn->exec("UPDATE menu SET name_original = title WHERE (name_original IS NULL OR name_original = '') AND title IS NOT NULL");
                break;
            case 'name_ru':
                $conn->exec("UPDATE menu SET name_ru = title WHERE (name_ru IS NULL OR name_ru = '') AND title IS NOT NULL");
                break;
            case 'name_lv':
                $conn->exec("UPDATE menu SET name_lv = title WHERE (name_lv IS NULL OR name_lv = '') AND title IS NOT NULL");
                break;
            case 'description_original':
                $conn->exec("UPDATE menu SET description_original = description WHERE (description_original IS NULL OR description_original = '') AND description IS NOT NULL");
                break;
            case 'description_ru':
                $conn->exec("UPDATE menu SET description_ru = description WHERE (description_ru IS NULL OR description_ru = '') AND description IS NOT NULL");
                break;
            case 'description_lv':
                $conn->exec("UPDATE menu SET description_lv = description WHERE (description_lv IS NULL OR description_lv = '') AND description IS NOT NULL");
                break;
            case 'category_original':
                $conn->exec("UPDATE menu SET category_original = category WHERE (category_original IS NULL OR category_original = '') AND category IS NOT NULL");
                break;
            case 'category_ru':
                $conn->exec("UPDATE menu SET category_ru = category WHERE (category_ru IS NULL OR category_ru = '') AND category IS NOT NULL");
                break;
            case 'category_lv':
                $conn->exec("UPDATE menu SET category_lv = category WHERE (category_lv IS NULL OR category_lv = '') AND category IS NOT NULL");
                break;
            case 'category_role':
                $conn->exec("UPDATE menu SET category_role = 'garnish' WHERE LOWER(category) LIKE '%гарнир%' OR LOWER(category) LIKE '%garnish%' OR LOWER(category) LIKE '%гарн%' OR LOWER(category) LIKE '%side%'");
                $conn->exec("UPDATE menu SET category_role = 'soup' WHERE LOWER(category) LIKE '%суп%' OR LOWER(category) LIKE '%soup%'");
                $conn->exec("UPDATE menu SET category_role = 'main' WHERE category_role IS NULL OR TRIM(category_role) = ''");
                break;
            case 'category_key':
                $conn->exec("UPDATE menu SET category_key = NULL");
                break;
            case 'ingredients_original':
                $conn->exec("UPDATE menu SET ingredients_original = ingredients WHERE (ingredients_original IS NULL OR ingredients_original = '') AND ingredients IS NOT NULL");
                break;
            case 'ingredients_ru':
                $conn->exec("UPDATE menu SET ingredients_ru = ingredients WHERE (ingredients_ru IS NULL OR ingredients_ru = '') AND ingredients IS NOT NULL");
                break;
            case 'ingredients_lv':
                $conn->exec("UPDATE menu SET ingredients_lv = ingredients WHERE (ingredients_lv IS NULL OR ingredients_lv = '') AND ingredients IS NOT NULL");
                break;
        }
    }
}

$orderColumns = [];
$orderInfo = $conn->query("PRAGMA table_info(orders)");
while ($row = $orderInfo->fetchArray(SQLITE3_ASSOC)) {
    $orderColumns[] = $row['name'];
}

if (!in_array('order_code', $orderColumns, true)) {
    $conn->exec("ALTER TABLE orders ADD COLUMN order_code TEXT");
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

$ordersWithoutCode = $conn->query("SELECT id FROM orders WHERE order_code IS NULL OR order_code = ''");
$existingCodes = [];
$codeChars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
while ($row = $ordersWithoutCode->fetchArray(SQLITE3_ASSOC)) {
    $id = (int)$row['id'];
    do {
        $length = random_int(4, 6);
        $code = 'CAF-';
        for ($i = 0; $i < $length; $i++) {
            $code .= $codeChars[random_int(0, strlen($codeChars) - 1)];
        }
        $escaped = $conn->escapeString($code);
        $exists = $conn->querySingle("SELECT 1 FROM orders WHERE order_code = '{$escaped}'");
    } while ($exists || isset($existingCodes[$code]));
    $existingCodes[$code] = true;
    $conn->exec("UPDATE orders SET order_code = '{$escaped}' WHERE id = {$id}");
}
$conn->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_orders_order_code ON orders(order_code)");

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
