<?php

namespace Quidque\Middleware;

use Quidque\Core\Auth;
use Quidque\Core\Csrf;

class AuthMiddleware
{
    public static function requireAuth(): bool|string
    {
        if (!Auth::check()) {
            http_response_code(401);
            header('Location: /login');
            exit;
        }
        return true;
    }
    
    public static function requireAdmin(): bool|string
    {
        if (!Auth::check()) {
            http_response_code(401);
            header('Location: /login');
            exit;
        }
        
        if (!Auth::isAdmin()) {
            http_response_code(403);
            return '403 Forbidden';
        }
        
        return true;
    }
    
    public static function requireGuest(): bool|string
    {
        if (Auth::check()) {
            header('Location: /');
            exit;
        }
        return true;
    }
    
    public static function verifyCsrf(): bool|string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify()) {
                http_response_code(403);
                return '403 Invalid CSRF token';
            }
        }
        return true;
    }
}