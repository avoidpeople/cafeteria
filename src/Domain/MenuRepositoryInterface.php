<?php

namespace App\Domain;

interface MenuRepositoryInterface
{
    /** @return string[] */
    public function getCategories(): array;

    /** @return MenuItem[] */
    public function findAll(string $search = '', string $category = '', bool $onlyToday = false): array;

    public function countAll(): int;

    public function findById(int $id): ?MenuItem;

    /** @return MenuItem[] */
    public function findByIds(array $ids): array;

    public function create(array $data): MenuItem;

    public function update(int $id, array $data): MenuItem;

    public function delete(int $id): void;

    /** @return MenuItem[] */
    public function findToday(string $search = '', string $category = ''): array;

    public function setTodayMenu(array $ids): void;

    public function getTodayIds(): array;

    public function countToday(): int;
}
