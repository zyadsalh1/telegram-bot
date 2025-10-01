<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $pattern, string $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, string $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, string $handler): void
    {
        $regex = '#^' . $pattern . '$#';
        $this->routes[$method][] = [$regex, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $routes = $this->routes[$method] ?? [];
        foreach ($routes as [$regex, $handler]) {
            if (preg_match($regex, $path, $matches)) {
                array_shift($matches);
                [$controllerName, $action] = explode('@', $handler);
                $controllerClass = 'App\\Controllers\\' . $controllerName;
                if (!class_exists($controllerClass)) {
                    http_response_code(404);
                    echo 'Controller not found';
                    return;
                }
                $controller = new $controllerClass();
                if (!method_exists($controller, $action)) {
                    http_response_code(404);
                    echo 'Action not found';
                    return;
                }
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }
        http_response_code(404);
        echo 'Not Found';
    }
}
?>
