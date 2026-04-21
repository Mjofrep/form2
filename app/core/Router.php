<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function __construct(private array $appConfig)
    {
    }

    public function get(string $path, array $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    private function map(string $method, string $path, array $handler): void
    {
        $this->routes[$method][] = [$path, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = request_path();
        $routes = $this->routes[strtoupper($method)] ?? [];

        foreach ($routes as [$routePath, $handler]) {
            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $params = array_filter($matches, static fn ($key): bool => !is_int($key), ARRAY_FILTER_USE_KEY);
            [$class, $action] = $handler;
            $controller = new $class();
            $controller->$action(...array_values($params));
            return;
        }

        http_response_code(404);
        render('home/404', ['title' => '404']);
    }
}
