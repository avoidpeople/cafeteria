<?php

namespace App\Application\Controller;

use App\Application\Service\MenuService;
use App\Domain\MenuItem;
use App\Infrastructure\ViewRenderer;

class MenuController
{
    public function __construct(private MenuService $menuService, private ViewRenderer $view)
    {
    }

    public function index(): string
    {
        $search = trim($_GET['search'] ?? '');
        $selectedCategory = trim($_GET['category'] ?? '');
        $categories = $this->menuService->categories();
        $menuItems = $this->menuService->menuItems($search, $selectedCategory);
        $comboOptions = $this->buildComboOptions($menuItems);

        return $this->view->render('menu', compact('search', 'selectedCategory', 'categories', 'menuItems', 'comboOptions'));
    }

    /**
     * @param MenuItem[] $menuItems
     */
    private function buildComboOptions(array $menuItems): array
    {
        $mains = [];
        $soups = [];
        $roles = [];
        foreach ($menuItems as $item) {
            if (!$item instanceof MenuItem) {
                continue;
            }
            $category = mb_strtolower(trim($item->category ?? ''));
            if ($category !== '') {
                if ($this->categoryMatches($category, ['суп', 'soup', 'борщ'])) {
                    $soups[] = $item;
                    $roles[$item->id] = 'soup';
                }
                if ($this->categoryMatches($category, ['горяч', 'основ', 'main', 'second', 'гриль'])) {
                    $mains[] = $item;
                    $roles[$item->id] = $roles[$item->id] ?? 'main';
                }
            }
        }

        if (empty($mains)) {
            $mains = $menuItems;
            foreach ($menuItems as $item) {
                if ($item instanceof MenuItem) {
                    $roles[$item->id] = $roles[$item->id] ?? 'main';
                }
            }
        } else {
            foreach ($mains as $item) {
                $roles[$item->id] = $roles[$item->id] ?? 'main';
            }
        }

        return [
            'main' => $this->mapComboItems($mains, 'main'),
            'soup' => $this->mapComboItems($soups, 'soup'),
            'roles' => $roles,
        ];
    }

    /** @param MenuItem[] $items */
    private function mapComboItems(array $items, string $role): array
    {
        return array_values(array_map(static fn (MenuItem $item) => [
            'id' => $item->id,
            'title' => $item->title,
            'category' => $item->category ?? 'Без категории',
            'role' => $role,
            'image' => $item->primaryImage(),
            'description' => $item->description ?? null,
            'price' => $item->price,
            'unique' => $item->isUnique(),
        ], $items));
    }

    private function categoryMatches(string $category, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($category, $needle)) {
                return true;
            }
        }
        return false;
    }
}
