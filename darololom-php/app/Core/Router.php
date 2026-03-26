<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<int, array{pattern:string, handler:string}>> */
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, string $handler): void
    {
        $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        $this->routes[$method][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function dispatch(string $method, string $path): void
    {
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            [$controllerName, $action] = explode('@', $route['handler']);
            $fqcn = 'App\\Controllers\\' . $controllerName;

            if (!class_exists($fqcn) || !method_exists($fqcn, $action)) {
                http_response_code(500);
                echo 'Controller/action not found.';
                return;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $params[$key] = $value;
                }
            }

            (new $fqcn())->{$action}($params);
            return;
        }

        http_response_code(404);
        echo '404 - Page not found';
    }
}
