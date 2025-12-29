<?php

namespace Quidque\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    
    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    private function addRoute(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $pattern = $this->pathToPattern($path);
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }
    
    public function addMiddleware(string $name, callable $middleware): void
    {
        $this->middlewares[$name] = $middleware;
    }
    
    public function dispatch(string $method, string $uri): mixed
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                foreach ($route['middleware'] as $name) {
                    if (isset($this->middlewares[$name])) {
                        $result = ($this->middlewares[$name])();
                        if ($result !== true) {
                            return $result;
                        }
                    }
                }
                
                return $this->callHandler($route['handler'], $params);
            }
        }
        
        http_response_code(404);
        return '404 Not Found';
    }
    
    private function pathToPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    private function callHandler(callable|array $handler, array $params): mixed
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $instance = new $class();
            return $instance->$method($params);
        }
        
        return $handler($params);
    }
}