<?php

namespace App\Application\Controller;

use App\Application\Service\MenuService;
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

        return $this->view->render('menu', compact('search', 'selectedCategory', 'categories', 'menuItems'));
    }
}
