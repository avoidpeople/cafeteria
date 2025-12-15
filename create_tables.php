<?php
include 'config/db.php';

// Таблица пользователей
$conn->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE CHECK(LENGTH(username) <= 60),
    password TEXT NOT NULL,
    first_name TEXT CHECK(LENGTH(first_name) <= 50),
    last_name TEXT CHECK(LENGTH(last_name) <= 50),
    phone TEXT CHECK(LENGTH(phone) <= 30),
    role TEXT CHECK(role IN ('user','admin')) DEFAULT 'user'
)");

// Таблица меню
$conn->exec("CREATE TABLE IF NOT EXISTS menu (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL CHECK(LENGTH(title) <= 180),
    description TEXT CHECK(LENGTH(description) <= 2000),
    ingredients TEXT CHECK(LENGTH(ingredients) <= 1000),
    allergens TEXT CHECK(LENGTH(allergens) <= 500),
    price REAL NOT NULL DEFAULT 0,
    use_manual_price INTEGER DEFAULT 0,
    image_url TEXT CHECK(LENGTH(image_url) <= 255),
    image_gallery TEXT CHECK(LENGTH(image_gallery) <= 8192),
    category TEXT CHECK(LENGTH(category) <= 120),
    is_today INTEGER DEFAULT 0,
    name_original TEXT CHECK(LENGTH(name_original) <= 180),
    name_ru TEXT CHECK(LENGTH(name_ru) <= 180),
    name_lv TEXT CHECK(LENGTH(name_lv) <= 180),
    description_original TEXT CHECK(LENGTH(description_original) <= 2000),
    description_ru TEXT CHECK(LENGTH(description_ru) <= 2000),
    description_lv TEXT CHECK(LENGTH(description_lv) <= 2000),
    category_original TEXT CHECK(LENGTH(category_original) <= 120),
    category_ru TEXT CHECK(LENGTH(category_ru) <= 120),
    category_lv TEXT CHECK(LENGTH(category_lv) <= 120),
    category_role TEXT DEFAULT 'main',
    category_key TEXT CHECK(LENGTH(category_key) <= 120),
    ingredients_original TEXT CHECK(LENGTH(ingredients_original) <= 1000),
    ingredients_ru TEXT CHECK(LENGTH(ingredients_ru) <= 1000),
    ingredients_lv TEXT CHECK(LENGTH(ingredients_lv) <= 1000)
)");

// Таблица заказов
$conn->exec("CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_code TEXT UNIQUE,
    user_id INTEGER NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    status TEXT CHECK(status IN ('pending','new','cooking','ready','delivered','cancelled')) DEFAULT 'pending',
    total_price REAL,
    delivery_address TEXT,
    comment TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

// Таблица содержимого заказов
$conn->exec("CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    menu_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    combo_details TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (menu_id) REFERENCES menu(id)
)");

$conn->exec("CREATE TABLE IF NOT EXISTS order_status_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    old_status TEXT,
    new_status TEXT NOT NULL,
    changed_by INTEGER,
    changed_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
)");
$conn->exec("CREATE INDEX IF NOT EXISTS idx_order_status_history_order ON order_status_history(order_id)");

$conn->exec("CREATE TABLE IF NOT EXISTS login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    rate_key TEXT NOT NULL,
    username TEXT,
    ip TEXT,
    attempted_at INTEGER NOT NULL,
    successful INTEGER DEFAULT 0,
    blocked_until INTEGER
)");
$conn->exec("CREATE INDEX IF NOT EXISTS idx_login_attempts_key_time ON login_attempts(rate_key, attempted_at)");


echo "Таблицы успешно созданы!";
?>
