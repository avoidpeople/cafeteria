<?php

namespace App\Application\Controller;

use App\Application\Service\MenuService;
use App\Infrastructure\ViewRenderer;

class HomeController
{
    public function __construct(private MenuService $menuService, private ViewRenderer $view)
    {
    }

    public function index(): string
    {
        $menuCount = $this->menuService->todayCount();
        return $this->view->render('home', [
            'title' => 'Cafeteria — Главная',
            'menuCount' => $menuCount,
        ]);
    }
}
