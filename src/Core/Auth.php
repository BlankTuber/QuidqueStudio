<?php

namespace Quidque\Core;

use Quidque\Models\User;
use Quidque\Models\AuthToken;
use Quidque\Models\Session;
use Quidque\Helpers\Mail;

class Auth
{
    private static array $config;
    private static ?array $user = null;
    private static ?string $sessionId = null;
    
    public static function init(array $config): void
    {
        self::$config = $config;
        self::loadSession();
    }
    
    private static function loadSession(): void
    {
        $cookieName = self::$config['session']['cookie_name'];
        
        if (!isset($_COOKIE[$cookieName])) {
            return;
        }
        
        self::$sessionId = $_COOKIE[$cookieName];
        $session = Session::findValid(self::$sessionId);
        
        if ($session) {
            self::$user = $session;
            Session::refresh(self::$sessionId, self::$config['session']['lifetime']);
        } else {
            self::clearCookie();
        }
    }
    
    public static function user(): ?array
    {
        return self::$user;
    }
    
    public static function id(): ?int
    {
        return self::$user['user_id'] ?? null;
    }
    
    public static function check(): bool
    {
        return self::$user !== null;
    }
    
    public static function isAdmin(): bool
    {
        return self::check() && (self::$user['is_admin'] ?? false);
    }
    
    public static function isConfirmed(): bool
    {
        return self::check() && (self::$user['is_confirmed'] ?? false);
    }
    
    public static function requestLogin(string $email): array
    {
        $user = User::findByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'error' => 'Email not found'];
        }
        
        if (!AuthToken::canRequestNew($user['id'], self::$config['auth']['token_cooldown'])) {
            return ['success' => false, 'error' => 'Please wait before requesting another login link'];
        }
        
        $token = AuthToken::generate($user['id'], self::$config['auth']['token_expiry']);
        
        Mail::sendLoginToken($email, $token);
        
        return ['success' => true];
    }
    
    public static function register(string $email, string $username): array
    {
        if (User::emailExists($email)) {
            return ['success' => false, 'error' => 'Email already registered'];
        }
        
        if (User::usernameExists($username)) {
            return ['success' => false, 'error' => 'Username already taken'];
        }
        
        if (!self::validateEmail($email)) {
            return ['success' => false, 'error' => 'Invalid email format'];
        }
        
        if (!self::validateUsername($username)) {
            return ['success' => false, 'error' => 'Username must be 3-50 characters, letters, numbers, underscores only'];
        }
        
        $userId = User::register($email, $username);
        
        $token = AuthToken::generate($userId, self::$config['auth']['token_expiry']);
        Mail::sendLoginToken($email, $token);
        
        return ['success' => true, 'user_id' => $userId];
    }
    
    public static function verifyToken(string $token): array
    {
        $authToken = AuthToken::findValid($token);
        
        if (!$authToken) {
            return ['success' => false, 'error' => 'Invalid or expired token'];
        }
        
        AuthToken::markUsed($authToken['id']);
        
        $user = User::find($authToken['user_id']);
        
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        if (!$user['is_confirmed']) {
            User::confirm($user['id']);
        }
        
        $deviceInfo = self::getDeviceInfo();
        
        $sessionId = Session::generate(
            $user['id'],
            self::$config['session']['lifetime'],
            $deviceInfo
        );
        
        self::setCookie($sessionId);
        
        Mail::sendNewLoginAlert($user['email'], $deviceInfo);
        
        return ['success' => true, 'user' => $user];
    }
    
    public static function logout(): void
    {
        if (self::$sessionId) {
            Session::destroy(self::$sessionId);
        }
        
        self::clearCookie();
        self::$user = null;
        self::$sessionId = null;
    }
    
    public static function logoutAll(): void
    {
        if (self::$user) {
            Session::destroyAllForUser(self::$user['user_id']);
        }
        
        self::clearCookie();
        self::$user = null;
        self::$sessionId = null;
    }
    
    private static function setCookie(string $sessionId): void
    {
        $cookieName = self::$config['session']['cookie_name'];
        $lifetime = self::$config['session']['lifetime'];
        
        setcookie($cookieName, $sessionId, [
            'expires' => time() + $lifetime,
            'path' => '/',
            'secure' => !self::$config['site']['debug'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    
    private static function clearCookie(): void
    {
        $cookieName = self::$config['session']['cookie_name'];
        
        setcookie($cookieName, '', [
            'expires' => time() - 3600,
            'path' => '/',
        ]);
    }
    
    private static function getDeviceInfo(): array
    {
        return [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 512),
            'country' => null,
            'city' => null,
        ];
    }
    
    private static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    private static function validateUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username) === 1;
    }
}