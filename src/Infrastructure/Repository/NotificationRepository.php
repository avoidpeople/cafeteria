<?php

namespace App\Infrastructure\Repository;

use App\Domain\Notification;
use App\Domain\NotificationRepositoryInterface;
use SQLite3;
use SQLite3Stmt;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(private SQLite3 $db)
    {
    }

    public function add(int $userId, int $orderId, string $status, string $message, float $amount = 0): void
    {
        $stmt = $this->prepare("INSERT INTO notifications (user_id, order_id, status, message, amount) VALUES (:uid, :oid, :status, :message, :amount)");
        $stmt->bindValue(':uid', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':oid', $orderId, SQLITE3_INTEGER);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':message', $message, SQLITE3_TEXT);
        $stmt->bindValue(':amount', $amount, SQLITE3_FLOAT);
        $stmt->execute();
    }

    public function latestForUser(int $userId, ?int $limit = null): array
    {
        $sql = "SELECT notifications.*, orders.order_code FROM notifications
                LEFT JOIN orders ON orders.id = notifications.order_id
                WHERE notifications.user_id = :uid
                ORDER BY notifications.id DESC";
        if ($limit !== null) {
            $limit = max(1, $limit);
            $sql .= " LIMIT {$limit}";
        }
        $stmt = $this->prepare($sql);
        $stmt->bindValue(':uid', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = new Notification(
                id: (int)$row['id'],
                userId: (int)$row['user_id'],
                orderId: (int)$row['order_id'],
                orderCode: $row['order_code'] ?? null,
                status: $row['status'],
                message: $row['message'],
                amount: (float)($row['amount'] ?? 0),
                createdAt: $row['created_at'],
            );
        }
        return $items;
    }

    public function clearForUser(int $userId): void
    {
        $stmt = $this->prepare("DELETE FROM notifications WHERE user_id = :uid");
        $stmt->bindValue(':uid', $userId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    private function prepare(string $sql): SQLite3Stmt
    {
        return $this->db->prepare($sql);
    }
}
