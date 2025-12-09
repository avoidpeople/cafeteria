<?php

namespace App\Infrastructure;

use function translate;

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function addRoute(string $method, string $path, callable $handler): void
    {
        $normalized = $this->normalizePath($path);
        $this->routes[$method][$normalized] = $handler;
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $path = $this->normalizePath(parse_url($uri, PHP_URL_PATH) ?: '/');
        $method = strtoupper($method);

        if (isset($this->routes[$method][$path])) {
            return call_user_func($this->routes[$method][$path]);
        }

        http_response_code(404);
        return translate('errors.not_found');
    }

    private function normalizePath(string $path): string
    {
        $path = rtrim($path, '/');
        return $path === '' ? '/' : $path;
    }

    public function routes(): array
    {
        return $this->routes;
    }
}
