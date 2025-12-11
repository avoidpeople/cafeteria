<?php

namespace App\Application;

use App\Infrastructure\Router;
use App\Infrastructure\ViewRenderer;

class App
{
    private Router $router;
    private ViewRenderer $view;

    private function __construct(Router $router, ViewRenderer $view)
    {
        $this->router = $router;
        $this->view = $view;
    }

    public static function create(ViewRenderer $view, ?Router $router = null): self
    {
        return new self($router ?? new Router(), $view);
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function view(): ViewRenderer
    {
        return $this->view;
    }

    public function run(): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return $this->router->dispatch($method, $uri);
    }
}
