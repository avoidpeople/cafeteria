<?php

namespace App\Infrastructure\Repository;

use App\Domain\Order;
use App\Domain\OrderItem;
use App\Domain\OrderRepositoryInterface;
use SQLite3;
use SQLite3Stmt;
use function translate;
use function currentLocale;

class OrderRepository implements OrderRepositoryInterface
{
    private bool $comboColumnChecked = false;
    private const ORDER_CODE_PREFIX = 'CAF-';
    private const ORDER_CODE_CHARS = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function __construct(private SQLite3 $db)
    {
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->prepare("SELECT * FROM orders WHERE user_id = :uid ORDER BY id DESC");
        $stmt->bindValue(':uid', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $orders = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $orders[] = $this->mapOrder($row);
        }
        return $orders;
    }

    public function findByCode(string $code): ?Order
    {
        $stmt = $this->prepare("SELECT orders.*, users.first_name, users.last_name, users.phone, users.username
            FROM orders
            LEFT JOIN users ON users.id = orders.user_id
            WHERE orders.order_code = :code");
        $stmt->bindValue(':code', $code, SQLITE3_TEXT);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        if (!$row) {
            return null;
        }
        $order = $this->mapOrder($row);
        if (isset($row['first_name']) || isset($row['last_name']) || isset($row['username'])) {
            $order->customerName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: ($row['username'] ?? null);
        }
        $order->customerPhone = $row['phone'] ?? null;
        return $order;
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->prepare("SELECT orders.*, users.first_name, users.last_name, users.phone, users.username
            FROM orders
            LEFT JOIN users ON users.id = orders.user_id
            WHERE orders.id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        if (!$row) {
            return null;
        }
        $order = $this->mapOrder($row);
        if (isset($row['first_name']) || isset($row['last_name']) || isset($row['username'])) {
            $order->customerName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: ($row['username'] ?? null);
        }
        $order->customerPhone = $row['phone'] ?? null;
        return $order;
    }

    public function findAll(?string $status = null, ?string $searchUser = null, bool $includePending = false): array
    {
        $sql = "SELECT orders.*, users.first_name, users.last_name, users.phone, users.username
                FROM orders
                JOIN users ON users.id = orders.user_id";
        $conditions = [];
        if ($status) {
            $conditions[] = "orders.status = :status";
        }
        if ($searchUser) {
            $conditions[] = "(users.username LIKE :u OR orders.order_code LIKE :u)";
        }
        if (!$includePending && !$status) {
            $conditions[] = "orders.status != 'pending'";
        }
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY orders.created_at DESC, orders.id DESC';
        $stmt = $this->prepare($sql);
        if ($status) {
            $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        }
        if ($searchUser) {
            $like = '%' . $searchUser . '%';
            $stmt->bindValue(':u', $like, SQLITE3_TEXT);
        }
        $result = $stmt->execute();
        $orders = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $order = $this->mapOrder($row);
            $order->customerName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: $row['username'];
            $order->customerPhone = $row['phone'] ?? null;
            $orders[] = $order;
        }
        return $orders;
    }

    public function updateStatus(int $orderId, string $status): void
    {
        $stmt = $this->prepare("UPDATE orders SET status = :st WHERE id = :id");
        $stmt->bindValue(':st', $status, SQLITE3_TEXT);
        $stmt->bindValue(':id', $orderId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function delete(int $orderId): void
    {
        $this->db->exec("DELETE FROM order_items WHERE order_id = " . (int)$orderId);
        $this->db->exec("DELETE FROM orders WHERE id = " . (int)$orderId);
    }

    public function create(int $userId, string $deliveryAddress, array $items, float $total, string $status = 'pending'): Order
    {
        $this->ensureComboColumn();
        $this->db->exec('BEGIN');
        try {
            $orderCode = $this->generateOrderCode();
            $stmt = $this->prepare("INSERT INTO orders (order_code, user_id, total_price, delivery_address, status) VALUES (:code, :uid, :total, :address, :status)");
            $stmt->bindValue(':code', $orderCode, SQLITE3_TEXT);
            $stmt->bindValue(':uid', $userId, SQLITE3_INTEGER);
            $stmt->bindValue(':total', $total, SQLITE3_FLOAT);
            $stmt->bindValue(':address', $deliveryAddress, SQLITE3_TEXT);
            $stmt->bindValue(':status', $status, SQLITE3_TEXT);
            $stmt->execute();
            $orderId = (int)$this->db->lastInsertRowID();

            foreach ($items as $item) {
                $stmt = $this->prepare("INSERT INTO order_items (order_id, menu_id, quantity, combo_details) VALUES (:oid, :mid, :qty, :combo)");
                $stmt->bindValue(':oid', $orderId, SQLITE3_INTEGER);
                $stmt->bindValue(':mid', $item['menu_id'], SQLITE3_INTEGER);
                $stmt->bindValue(':qty', $item['quantity'], SQLITE3_INTEGER);
                $comboPayload = $item['combo_details'] ?? null;
                $stmt->bindValue(':combo', $comboPayload ? json_encode($comboPayload, JSON_UNESCAPED_UNICODE) : null, SQLITE3_TEXT);
                $stmt->execute();
            }

            $this->db->exec('COMMIT');
            return $this->findById($orderId);
        } catch (\Throwable $e) {
            $this->db->exec('ROLLBACK');
            throw $e;
        }
    }

    public function summary(): array
    {
        $row = $this->db->querySingle("SELECT COUNT(CASE WHEN status != 'pending' THEN 1 END) AS total_orders,
                      SUM(total_price) AS total_sum,
                      SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_orders
               FROM orders WHERE status != 'pending'", true);
        return [
            'total_orders' => (int)($row['total_orders'] ?? 0),
            'new_orders' => (int)($row['new_orders'] ?? 0),
            'total_sum' => (float)($row['total_sum'] ?? 0),
        ];
    }

    public function userStats(int $userId): array
    {
        $stmt = $this->prepare("SELECT COUNT(*) AS orders, COALESCE(SUM(total_price), 0) AS total
            FROM orders WHERE user_id = :uid");
        $stmt->bindValue(':uid', $userId, SQLITE3_INTEGER);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return [
            'orders' => (int)($row['orders'] ?? 0),
            'total' => (float)($row['total'] ?? 0),
        ];
    }

    private function mapOrder(array $row): Order
    {
        $customerName = null;
        if (isset($row['first_name']) || isset($row['last_name']) || isset($row['username'])) {
            $customerName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            if ($customerName === '' && isset($row['username'])) {
                $customerName = $row['username'];
            }
        }

        $customerPhone = $row['phone'] ?? null;

        return new Order(
            id: (int)$row['id'],
            userId: (int)$row['user_id'],
            status: $row['status'],
            totalPrice: (float)$row['total_price'],
            createdAt: $row['created_at'],
            deliveryAddress: $row['delivery_address'] ?? null,
            orderCode: $row['order_code'] ?? null,
            items: $this->fetchItems((int)$row['id']),
            customerName: $customerName,
            customerPhone: $customerPhone
        );
    }

    /** @return OrderItem[] */
    private function fetchItems(int $orderId): array
    {
        $stmt = $this->prepare("SELECT order_items.*, menu.title, menu.price AS menu_price, menu.image_url,
            menu.name_original, menu.name_ru, menu.name_lv
            FROM order_items
            JOIN menu ON menu.id = order_items.menu_id
            WHERE order_items.order_id = :oid");
        $stmt->bindValue(':oid', $orderId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $comboDetails = null;
            if (!empty($row['combo_details'])) {
                $decoded = json_decode($row['combo_details'], true);
                if (is_array($decoded)) {
                    $comboDetails = $decoded;
                }
            }

            $price = (float)($row['menu_price'] ?? 0);
            if ($comboDetails && isset($comboDetails['price'])) {
                $price = (float)$comboDetails['price'];
            }

            $items[] = new OrderItem(
                menuId: (int)$row['menu_id'],
                title: $this->resolveOrderItemTitle($row),
                price: $price,
                quantity: (int)$row['quantity'],
                imageUrl: $row['image_url'] ?? null,
                comboDetails: $comboDetails
            );
        }
        return $items;
    }

    private function generateOrderCode(): string
    {
        $length = random_int(4, 6);
        $chars = self::ORDER_CODE_CHARS;
        do {
            $code = self::ORDER_CODE_PREFIX;
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $existsStmt = $this->prepare('SELECT 1 FROM orders WHERE order_code = :code LIMIT 1');
            $existsStmt->bindValue(':code', $code, SQLITE3_TEXT);
            $exists = $existsStmt->execute()->fetchArray(SQLITE3_ASSOC);
        } while ($exists);

        return $code;
    }

    public function findPending(): array
    {
        $sql = "SELECT orders.*, users.first_name, users.last_name, users.phone, users.username
                FROM orders
                JOIN users ON users.id = orders.user_id
                WHERE orders.status = 'pending'
                ORDER BY orders.created_at DESC";
        $result = $this->db->query($sql);
        $orders = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $order = $this->mapOrder($row);
            $order->customerName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: ($row['username'] ?? translate('orders.view.customer_unknown'));
            $order->customerPhone = $row['phone'] ?? null;
            $orders[] = $order;
        }
        return $orders;
    }

    public function countPending(): int
    {
        $row = $this->db->querySingle("SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'", true);
        return (int)($row['total'] ?? 0);
    }

    private function resolveOrderItemTitle(array $row): string
    {
        $locale = function_exists('currentLocale') ? currentLocale() : 'ru';
        $localized = [
            'ru' => $row['name_ru'] ?? null,
            'lv' => $row['name_lv'] ?? null,
        ];
        $value = $localized[$locale] ?? null;
        if (is_string($value) && trim($value) !== '') {
            return $value;
        }
        if (!empty($row['name_original'])) {
            return $row['name_original'];
        }
        if (!empty($row['title'])) {
            return $row['title'];
        }
        return translate('common.dish');
    }

    private function prepare(string $sql): SQLite3Stmt
    {
        return $this->db->prepare($sql);
    }

    private function ensureComboColumn(): void
    {
        if ($this->comboColumnChecked) {
            return;
        }
        $this->comboColumnChecked = true;
        $result = $this->db->query("PRAGMA table_info('order_items')");
        $hasColumn = false;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if (($row['name'] ?? '') === 'combo_details') {
                $hasColumn = true;
                break;
            }
        }
        if (!$hasColumn) {
            $this->db->exec("ALTER TABLE order_items ADD COLUMN combo_details TEXT");
        }
    }
}
