<?php

namespace App\Core;

use App\Middleware\MiddlewareInterface;
use Closure;

/**
 * Custom OOP Router with Middleware Pipeline and Route Parameters
 * Matching Class Diagram 5
 */
class Router
{
    private array $routes = [];
    private array $globalMiddleware = [];

    public function use(MiddlewareInterface|string $middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }

    public function get(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function patch(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware = []): self
    {
        $path = '/' . trim($path, '/');
        // Convert route parameters {id} into regex named groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $regex = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'regex'      => $regex,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];

        return $this;
    }

    public function dispatch(): void
    {
        $request = new Request();
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                $request->setRouteParams($params);

                // Build middleware chain
                $middlewares = array_merge($this->globalMiddleware, $route['middleware']);
                $handler = $route['handler'];

                $runner = $this->buildPipeline($middlewares, function (Request $req) use ($handler) {
                    return $this->executeHandler($handler, $req);
                });

                $runner($request);
                return;
            }
        }

        // Route Not Found (404)
        if ($request->isAjax() || $request->expectsJson()) {
            Response::json(['error' => 'Not Found', 'message' => 'Resource not found'], 404);
        }

        http_response_code(404);
        View::render('errors/404', ['uri' => $uri], null);
    }

    private function buildPipeline(array $middlewares, Closure $destination): Closure
    {
        return array_reduce(
            array_reverse($middlewares),
            function ($next, $middleware) {
                return function (Request $request) use ($middleware, $next) {
                    if (is_string($middleware)) {
                        $middlewareInstance = new $middleware();
                    } else {
                        $middlewareInstance = $middleware;
                    }

                    if ($middlewareInstance instanceof MiddlewareInterface) {
                        return $middlewareInstance->handle($request, $next);
                    }

                    return $next($request);
                };
            },
            $destination
        );
    }

    private function executeHandler(array|callable $handler, Request $request): mixed
    {
        if (is_callable($handler)) {
            return $handler($request);
        }

        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return $controller->$method($request);
        }

        throw new \RuntimeException("Invalid route handler format");
    }
}
