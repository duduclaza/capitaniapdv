<?php

namespace App\Core;

/**
 * Router - Simple HTTP router for MVC
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, $handler, array $middlewares): void
    {
        // Convert route params e.g. /produto/{id} to regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'      => $method,
            'path'        => $path,
            'pattern'     => $pattern,
            'handler'     => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        // Handle method override for PUT/DELETE via POST + _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            // Extract named params
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            // Run middlewares
            foreach ($route['middlewares'] as $middlewareClass) {
                $middleware = new $middlewareClass();
                $middleware->handle();
            }

            // Dispatch handler
            $handler = $route['handler'];

            if (is_array($handler)) {
                [$controllerClass, $methodName] = $handler;
                $controller = new $controllerClass();
                $controller->$methodName(...array_values($params));
            } elseif (is_callable($handler)) {
                $handler(...array_values($params));
            }

            return;
        }

        // 404
        http_response_code(404);
        $this->render404();
    }

    private function render404(): void
    {
        $view = VIEWS_PATH . '/errors/404.php';
        if (file_exists($view)) {
            include $view;
        } else {
            echo '<h1>404 - Página não encontrada</h1>';
        }
    }
}
