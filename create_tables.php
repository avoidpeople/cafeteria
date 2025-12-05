<?php
include 'config/db.php';

// Таблица пользователей
$conn->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    first_name TEXT,
    last_name TEXT,
    phone TEXT,
    role TEXT CHECK(role IN ('user','admin')) DEFAULT 'user'
)");

// Таблица меню
$conn->exec("CREATE TABLE IF NOT EXISTS menu (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    image_url TEXT,
    image_gallery TEXT,
    category TEXT,
    is_today INTEGER DEFAULT 0
)");

// Таблица заказов
$conn->exec("CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    status TEXT CHECK(status IN ('pending','new','cooking','ready','delivered','cancelled')) DEFAULT 'pending',
    total_price REAL,
    delivery_address TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

// Таблица содержимого заказов
$conn->exec("CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    menu_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (menu_id) REFERENCES menu(id)
)");


echo "Таблицы успешно созданы!";
?>
