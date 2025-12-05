<?php

namespace App\Application\Service;

use App\Domain\MenuRepositoryInterface;

class MenuService
{
    public function __construct(private MenuRepositoryInterface $repository)
    {
    }

    public function categories(): array
    {
        return $this->repository->getCategories();
    }

    public function menuItems(string $search = '', string $category = ''): array
    {
        return $this->repository->findAll($search, $category, true);
    }

    public function todayMenu(string $search = '', string $category = ''): array
    {
        return $this->repository->findToday($search, $category);
    }

    public function totalCount(): int
    {
        return $this->repository->countAll();
    }

    public function todayCount(): int
    {
        return $this->repository->countToday();
    }

    public function findItem(int $id): ?\App\Domain\MenuItem
    {
        return $this->repository->findById($id);
    }
}
