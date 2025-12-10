<?php

namespace App\Infrastructure\Repository;

use App\Domain\MenuItem;
use App\Domain\MenuRepositoryInterface;
use SQLite3;
use SQLite3Stmt;

class MenuRepository implements MenuRepositoryInterface
{
    public function __construct(private SQLite3 $db)
    {
    }

    public function getCategories(): array
    {
        $categories = [];
        $column = $this->categoryColumn();
        $result = $this->db->query("SELECT DISTINCT {$column} AS category_locale, category_original, category FROM menu WHERE {$column} IS NOT NULL OR category_original IS NOT NULL OR category IS NOT NULL");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $label = $row['category_locale'] ?? $row['category_original'] ?? $row['category'] ?? null;
            if (is_string($label)) {
                $label = trim($label);
            }
            if ($label !== null && $label !== '') {
                $categories[] = $label;
            }
        }
        sort($categories, SORT_NATURAL | SORT_FLAG_CASE);
        return array_values(array_unique($categories));
    }

    public function findAll(string $search = '', string $category = '', bool $onlyToday = false): array
    {
        $sql = "SELECT * FROM menu";
        $conditions = [];
        if ($search !== '') {
            $conditions[] = "(title LIKE :search OR description LIKE :search OR name_original LIKE :search OR name_ru LIKE :search OR name_lv LIKE :search
                OR description_original LIKE :search OR description_ru LIKE :search OR description_lv LIKE :search)";
        }
        if ($category !== '') {
            $column = $this->categoryColumn();
            $conditions[] = "(COALESCE({$column}, category_original, category) = :category)";
        }
        if ($onlyToday) {
            $conditions[] = "is_today = 1";
        }
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY title ASC';
        $stmt = $this->prepare($sql);
        if ($search !== '') {
            $like = '%' . $search . '%';
            $stmt->bindValue(':search', $like, SQLITE3_TEXT);
        }
        if ($category !== '') {
            $stmt->bindValue(':category', $category, SQLITE3_TEXT);
        }
        $result = $stmt->execute();
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = $this->mapMenuItem($row);
        }
        return $items;
    }

    public function countAll(): int
    {
        $row = $this->db->querySingle('SELECT COUNT(*) as total FROM menu', true);
        return (int)($row['total'] ?? 0);
    }

    public function findById(int $id): ?MenuItem
    {
        $stmt = $this->prepare('SELECT * FROM menu WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return $row ? $this->mapMenuItem($row) : null;
    }

    public function findByIds(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn ($id) => $id > 0));
        if (!$ids) {
            return [];
        }
        $sql = 'SELECT * FROM menu WHERE id IN (' . implode(',', $ids) . ')';
        $result = $this->db->query($sql);
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = $this->mapMenuItem($row);
        }
        return $items;
    }

    public function create(array $data): MenuItem
    {
        if (!array_key_exists('is_today', $data)) {
            $data['is_today'] = 0;
        }
        $stmt = $this->prepare("INSERT INTO menu (title, description, ingredients, price, category, image_url, image_gallery, is_today, use_manual_price,
            name_original, name_ru, name_lv, description_original, description_ru, description_lv, category_original, category_ru, category_lv,
            category_role, category_key, ingredients_original, ingredients_ru, ingredients_lv, allergens)
            VALUES (:title, :description, :ingredients, :price, :category, :image_url, :image_gallery, :is_today, :use_manual_price,
            :name_original, :name_ru, :name_lv, :description_original, :description_ru, :description_lv, :category_original, :category_ru, :category_lv,
            :category_role, :category_key, :ingredients_original, :ingredients_ru, :ingredients_lv, :allergens)");
        $this->bindCommonFields($stmt, $data);
        $stmt->execute();
        $data['id'] = (int)$this->db->lastInsertRowID();
        return $this->mapMenuItem($data);
    }

    public function update(int $id, array $data): MenuItem
    {
        if (!array_key_exists('is_today', $data)) {
            $existing = $this->findById($id);
            $data['is_today'] = $existing? ($existing->isToday ? 1 : 0) : 0;
        }
        $stmt = $this->prepare("UPDATE menu SET title = :title, description = :description, ingredients = :ingredients,
            price = :price, category = :category, image_url = :image_url, image_gallery = :image_gallery, is_today = :is_today,
            use_manual_price = :use_manual_price, name_original = :name_original, name_ru = :name_ru, name_lv = :name_lv,
            description_original = :description_original, description_ru = :description_ru, description_lv = :description_lv,
            category_original = :category_original, category_ru = :category_ru, category_lv = :category_lv,
            category_role = :category_role, category_key = :category_key,
            ingredients_original = :ingredients_original, ingredients_ru = :ingredients_ru, ingredients_lv = :ingredients_lv, allergens = :allergens WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $this->bindCommonFields($stmt, $data);
        $stmt->execute();
        $data['id'] = $id;
        return $this->mapMenuItem($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->prepare("DELETE FROM menu WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    private function bindCommonFields(SQLite3Stmt $stmt, array $data): void
    {
        $title = $data['title'] ?? ($data['name_original'] ?? '');
        $description = $data['description'] ?? ($data['description_original'] ?? '');
        $category = $data['category'] ?? ($data['category_original'] ?? '');

        $stmt->bindValue(':title', $title, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $ingredients = $data['ingredients'] ?? ($data['ingredients_original'] ?? '');
        $stmt->bindValue(':ingredients', $ingredients, SQLITE3_TEXT);
        $stmt->bindValue(':price', (float)($data['price'] ?? 0), SQLITE3_FLOAT);
        $stmt->bindValue(':category', $category, SQLITE3_TEXT);
        $stmt->bindValue(':image_url', $data['image_url'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':image_gallery', !empty($data['image_gallery']) ? json_encode($data['image_gallery']) : null, SQLITE3_TEXT);
        $stmt->bindValue(':is_today', !empty($data['is_today']) ? 1 : 0, SQLITE3_INTEGER);
        $stmt->bindValue(':use_manual_price', !empty($data['use_manual_price']) ? 1 : 0, SQLITE3_INTEGER);
        $stmt->bindValue(':name_original', $data['name_original'] ?? $title, SQLITE3_TEXT);
        $stmt->bindValue(':name_ru', $data['name_ru'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':name_lv', $data['name_lv'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':description_original', $data['description_original'] ?? $description, SQLITE3_TEXT);
        $stmt->bindValue(':description_ru', $data['description_ru'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':description_lv', $data['description_lv'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':category_original', $data['category_original'] ?? $category, SQLITE3_TEXT);
        $stmt->bindValue(':category_ru', $data['category_ru'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':category_lv', $data['category_lv'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':category_role', $data['category_role'] ?? 'main', SQLITE3_TEXT);
        $stmt->bindValue(':category_key', $data['category_key'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':ingredients_original', $data['ingredients_original'] ?? $ingredients, SQLITE3_TEXT);
        $stmt->bindValue(':ingredients_ru', $data['ingredients_ru'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':ingredients_lv', $data['ingredients_lv'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':allergens', $data['allergens'] ?? null, SQLITE3_TEXT);
    }

    private function mapMenuItem(array $row): MenuItem
    {
        $gallery = [];
        $galleryField = $row['image_gallery'] ?? null;
        if (is_array($galleryField)) {
            $gallery = array_values($galleryField);
        } elseif (is_string($galleryField) && $galleryField !== '') {
            $decoded = json_decode($galleryField, true);
            if (is_array($decoded)) {
                $gallery = array_values($decoded);
            }
        }

        return new MenuItem(
            id: (int)$row['id'],
            nameOriginal: $row['name_original'] ?? null,
            nameRu: $row['name_ru'] ?? null,
            nameLv: $row['name_lv'] ?? null,
            descriptionOriginal: $row['description_original'] ?? null,
            descriptionRu: $row['description_ru'] ?? null,
            descriptionLv: $row['description_lv'] ?? null,
            ingredientsOriginal: $row['ingredients_original'] ?? null,
            ingredientsRu: $row['ingredients_ru'] ?? null,
            ingredientsLv: $row['ingredients_lv'] ?? null,
            allergens: $row['allergens'] ?? null,
            price: (float)$row['price'],
            useManualPrice: !empty($row['use_manual_price'] ?? 0),
            categoryOriginal: $row['category_original'] ?? null,
            categoryRu: $row['category_ru'] ?? null,
            categoryLv: $row['category_lv'] ?? null,
            categoryRole: $row['category_role'] ?? 'main',
            categoryKey: $row['category_key'] ?? null,
            imageUrl: $row['image_url'] ?? null,
            gallery: $gallery,
            isToday: !empty($row['is_today']),
            legacyTitle: $row['title'] ?? null,
            legacyDescription: $row['description'] ?? null,
            legacyCategory: $row['category'] ?? null,
            legacyIngredients: $row['ingredients'] ?? null,
        );
    }

    public function findToday(string $search = '', string $category = ''): array
    {
        return $this->findAll($search, $category, true);
    }

    public function setTodayMenu(array $ids): void
    {
        $this->db->exec("UPDATE menu SET is_today = 0");
        $filtered = array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
        if ($filtered) {
            $this->db->exec('UPDATE menu SET is_today = 1 WHERE id IN (' . implode(',', $filtered) . ')');
        }
    }

    public function getTodayIds(): array
    {
        $ids = [];
        $result = $this->db->query("SELECT id FROM menu WHERE is_today = 1");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $ids[] = (int)$row['id'];
        }
        return $ids;
    }

    public function countToday(): int
    {
        $row = $this->db->querySingle('SELECT COUNT(*) as total FROM menu WHERE is_today = 1', true);
        return (int)($row['total'] ?? 0);
    }

    private function categoryColumn(): string
    {
        $locale = $this->detectLocale();
        return match ($locale) {
            'lv' => 'category_lv',
            default => 'category_ru',
        };
    }

    private function detectLocale(): string
    {
        if (function_exists('currentLocale')) {
            return currentLocale();
        }
        return 'ru';
    }

    private function prepare(string $sql): SQLite3Stmt
    {
        return $this->db->prepare($sql);
    }
}
