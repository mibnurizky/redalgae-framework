<?php
namespace RedAlgae\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private ?string $lastRouteKey = null; // track route terakhir untuk ->name()

    public function get(string $path, $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, $handler): self
    {
        $key = $method . ':' . $path;

        $this->routes[$key] = [
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler,
            'pattern' => $this->convertToRegex($path),
            'name'    => null,
        ];

        $this->lastRouteKey = $key;

        return $this;
    }

    // Beri nama ke route terakhir
    public function name(string $name): self
    {
        if ($this->lastRouteKey && isset($this->routes[$this->lastRouteKey])) {
            $this->routes[$this->lastRouteKey]['name'] = $name;
        }

        return $this;
    }

    // Generate URL dari nama route
    public function route(string $name, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if ($route['name'] !== $name) continue;

            $path = $route['path'];

            // Replace {param} dengan nilai dari $params
            foreach ($params as $key => $value) {
                $path = str_replace('{' . $key . '}', $value, $path);
            }

            // Kalau masih ada {param} yang belum diganti
            if (preg_match('/\{[a-zA-Z_]+\}/', $path)) {
                throw new \Exception("Missing parameters for route [{$name}].");
            }

            return $path;
        }

        throw new \Exception("Route name [{$name}] not found.");
    }

    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->runHandler($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        COMPONENT->includeView('error.404');
    }

    private function runHandler($handler, array $params): void
    {
        // Closure
        if ($handler instanceof \Closure) {
            call_user_func_array($handler, $params);
            return;
        }

        // Array [Controller::class, 'method']
        if (is_array($handler)) {
            [$class, $method] = $handler;

            if (!class_exists($class)) {
                throw new \Exception("Controller [{$class}] not found.");
            }

            $controller = new $class();

            if (!method_exists($controller, $method)) {
                throw new \Exception("Method [{$method}] not found in [{$class}].");
            }

            call_user_func_array([$controller, $method], $params);
            return;
        }

        if (is_string($handler)) {
            if (str_contains($handler, '@')) {
                [$class, $method] = explode('@', $handler);
            } else {
                $class  = $handler;
                $method = '__invoke';
            }

            if (!class_exists($class)) {
                throw new \Exception("Controller [{$class}] not found.");
            }

            $controller = new $class();

            if (!method_exists($controller, $method)) {
                throw new \Exception("Method [{$method}] not found in [{$class}].");
            }

            call_user_func_array([$controller, $method], $params);
            return;
        }

        throw new \Exception("Handler not valid.");
    }
}