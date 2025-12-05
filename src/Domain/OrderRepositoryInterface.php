<?php

namespace App\Domain;

interface OrderRepositoryInterface
{
    /** @return Order[] */
    public function findByUser(int $userId): array;

    public function findById(int $id): ?Order;

    /** @return Order[] */
    public function findAll(?string $status = null, ?string $searchUser = null, bool $includePending = false): array;

    public function updateStatus(int $orderId, string $status): void;

    public function delete(int $orderId): void;

    /**
     * @param array<array{menu_id:int, quantity:int, price:float}> $items
     */
    public function create(int $userId, string $deliveryAddress, array $items, float $total, string $status = 'pending'): Order;

    /**
     * @return array{total_orders:int,new_orders:int,total_sum:float}
     */
    public function summary(): array;

    /**
     * @return array{orders:int,total:float}
     */
    public function userStats(int $userId): array;

    /** @return Order[] */
    public function findPending(): array;

    public function countPending(): int;
}
