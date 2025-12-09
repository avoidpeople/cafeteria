<?php

namespace App\Application\Controller;

use App\Application\Service\MenuService;
use App\Infrastructure\ViewRenderer;
use function translate;

class HomeController
{
    public function __construct(private MenuService $menuService, private ViewRenderer $view)
    {
    }

    public function index(): string
    {
        $menuCount = $this->menuService->todayCount();
        return $this->view->render('home', [
            'title' => 'Doctor Gorilka — ' . translate('home.title'),
            'menuCount' => $menuCount,
        ]);
    }
}
