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
        $result = $this->db->query("SELECT DISTINCT category FROM menu WHERE category IS NOT NULL AND TRIM(category) != '' ORDER BY category ASC");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $categories[] = $row['category'];
        }
        return $categories;
    }

    public function findAll(string $search = '', string $category = '', bool $onlyToday = false): array
    {
        $sql = "SELECT * FROM menu";
        $conditions = [];
        if ($search !== '') {
            $conditions[] = "(title LIKE :search OR description LIKE :search)";
        }
        if ($category !== '') {
            $conditions[] = "category = :category";
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
        $stmt = $this->prepare("INSERT INTO menu (title, description, ingredients, price, category, image_url, image_gallery, is_today, use_manual_price)
            VALUES (:title, :description, :ingredients, :price, :category, :image_url, :image_gallery, :is_today, :use_manual_price)");
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
            use_manual_price = :use_manual_price WHERE id = :id");
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
        $stmt->bindValue(':title', $data['title'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':description', $data['description'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':ingredients', $data['ingredients'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':price', (float)($data['price'] ?? 0), SQLITE3_FLOAT);
        $stmt->bindValue(':category', $data['category'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':image_url', $data['image_url'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':image_gallery', !empty($data['image_gallery']) ? json_encode($data['image_gallery']) : null, SQLITE3_TEXT);
        $stmt->bindValue(':is_today', !empty($data['is_today']) ? 1 : 0, SQLITE3_INTEGER);
        $stmt->bindValue(':use_manual_price', !empty($data['use_manual_price']) ? 1 : 0, SQLITE3_INTEGER);
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
            title: $row['title'],
            description: $row['description'] ?? null,
            ingredients: $row['ingredients'] ?? null,
            price: (float)$row['price'],
            useManualPrice: !empty($row['use_manual_price'] ?? 0),
            category: $row['category'] ?? null,
            imageUrl: $row['image_url'] ?? null,
            gallery: $gallery,
            isToday: !empty($row['is_today']),
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

    private function prepare(string $sql): SQLite3Stmt
    {
        return $this->db->prepare($sql);
    }
}
