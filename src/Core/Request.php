<?php

namespace Quidque\Core;

class Request
{
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public function uri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
    
    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }
    
    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }
    
    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }
    
    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    public function isHtmx(): bool
    {
        return isset($_SERVER['HTTP_HX_REQUEST']);
    }
}