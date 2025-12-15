<?php
declare(strict_types=1);

require __DIR__ . '/../../config/db.php';

function tableHasConstraint(SQLite3 $conn, string $table, string $needle): bool
{
    $sql = $conn->querySingle("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'");
    return $sql !== null && strpos($sql, $needle) !== false;
}

function rebuildUsers(SQLite3 $conn): void
{
    if (tableHasConstraint($conn, 'users', 'CHECK(LENGTH(username)')) {
        return;
    }
    $conn->exec('PRAGMA foreign_keys = OFF');
    $conn->exec('BEGIN TRANSACTION');
    $conn->exec('ALTER TABLE users RENAME TO users_old_limits');
    $conn->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE CHECK(LENGTH(username) <= 60),
        password TEXT NOT NULL,
        first_name TEXT CHECK(LENGTH(first_name) <= 50),
        last_name TEXT CHECK(LENGTH(last_name) <= 50),
        phone TEXT CHECK(LENGTH(phone) <= 30),
        role TEXT CHECK(role IN ('user','admin')) DEFAULT 'user'
    )");
    $conn->exec("INSERT INTO users (id, username, password, first_name, last_name, phone, role)
        SELECT id,
            SUBSTR(username, 1, 60),
            password,
            SUBSTR(first_name, 1, 50),
            SUBSTR(last_name, 1, 50),
            SUBSTR(phone, 1, 30),
            COALESCE(role, 'user')
        FROM users_old_limits");
    $conn->exec('DROP TABLE users_old_limits');
    $conn->exec('COMMIT');
    $conn->exec('PRAGMA foreign_keys = ON');
}

function rebuildMenu(SQLite3 $conn): void
{
    if (tableHasConstraint($conn, 'menu', 'CHECK(LENGTH(title)')) {
        return;
    }
    $conn->exec('PRAGMA foreign_keys = OFF');
    $conn->exec('BEGIN TRANSACTION');
    $conn->exec('ALTER TABLE menu RENAME TO menu_old_limits');
    $conn->exec("CREATE TABLE menu (
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
    $conn->exec("INSERT INTO menu (id, title, description, ingredients, allergens, price, use_manual_price, image_url, image_gallery, category, is_today,
        name_original, name_ru, name_lv, description_original, description_ru, description_lv, category_original, category_ru, category_lv,
        category_role, category_key, ingredients_original, ingredients_ru, ingredients_lv)
        SELECT id,
            SUBSTR(COALESCE(title, ''), 1, 180),
            SUBSTR(description, 1, 2000),
            SUBSTR(ingredients, 1, 1000),
            SUBSTR(allergens, 1, 500),
            price,
            COALESCE(use_manual_price, 0),
            SUBSTR(image_url, 1, 255),
            SUBSTR(image_gallery, 1, 8192),
            SUBSTR(category, 1, 120),
            COALESCE(is_today, 0),
            SUBSTR(name_original, 1, 180),
            SUBSTR(name_ru, 1, 180),
            SUBSTR(name_lv, 1, 180),
            SUBSTR(description_original, 1, 2000),
            SUBSTR(description_ru, 1, 2000),
            SUBSTR(description_lv, 1, 2000),
            SUBSTR(category_original, 1, 120),
            SUBSTR(category_ru, 1, 120),
            SUBSTR(category_lv, 1, 120),
            COALESCE(category_role, 'main'),
            SUBSTR(category_key, 1, 120),
            SUBSTR(ingredients_original, 1, 1000),
            SUBSTR(ingredients_ru, 1, 1000),
            SUBSTR(ingredients_lv, 1, 1000)
        FROM menu_old_limits");
    $conn->exec('DROP TABLE menu_old_limits');
    $conn->exec('COMMIT');
    $conn->exec('PRAGMA foreign_keys = ON');
}

rebuildUsers($conn);
rebuildMenu($conn);

echo "Length constraints applied for users and menu tables.\n";
