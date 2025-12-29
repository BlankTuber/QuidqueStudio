<?php

namespace Quidque\Core;

class Csrf
{
    private static string $tokenName = 'csrf_token';
    
    public static function generate(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION[self::$tokenName])) {
            $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION[self::$tokenName];
    }
    
    public static function token(): string
    {
        return self::generate();
    }
    
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . self::token() . '">';
    }
    
    public static function verify(?string $token = null): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = $token ?? $_POST[self::$tokenName] ?? '';
        $sessionToken = $_SESSION[self::$tokenName] ?? '';
        
        if (empty($token) || empty($sessionToken)) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
    
    public static function regenerate(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
        return $_SESSION[self::$tokenName];
    }
}