<?php

namespace Quidque\Controllers;

use Quidque\Core\Auth;
use Quidque\Helpers\RateLimiter;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_DECAY_SECONDS = 900; // 15 minutes
    
    public function loginForm(array $params): string
    {
        return $this->render('auth/login');
    }
    
    public function login(array $params): string
    {
        $email = trim($this->request->post('email', ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if (empty($email)) {
            return $this->render('auth/login', ['error' => 'Email is required']);
        }
        
        $rateLimitKey = 'login:' . $ip;
        
        if (!RateLimiter::attempt($rateLimitKey, self::MAX_LOGIN_ATTEMPTS, self::LOGIN_DECAY_SECONDS)) {
            $waitTime = RateLimiter::availableIn($rateLimitKey, self::LOGIN_DECAY_SECONDS);
            $minutes = ceil($waitTime / 60);
            return $this->render('auth/login', [
                'error' => "Too many login attempts. Please try again in {$minutes} minute(s)."
            ]);
        }
        
        $result = Auth::requestLogin($email);
        
        if ($result['success']) {
            RateLimiter::clear($rateLimitKey);
            return $this->render('auth/login', ['success' => 'Check your email for a login link']);
        }
        
        return $this->render('auth/login', ['error' => $result['error']]);
    }
    
    public function registerForm(array $params): string
    {
        return $this->render('auth/register');
    }
    
    public function register(array $params): string
    {
        $email = trim($this->request->post('email', ''));
        $username = trim($this->request->post('username', ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if (empty($email) || empty($username)) {
            return $this->render('auth/register', ['error' => 'All fields are required']);
        }
        
        $rateLimitKey = 'register:' . $ip;
        
        if (!RateLimiter::attempt($rateLimitKey, 3, 3600)) {
            return $this->render('auth/register', [
                'error' => 'Too many registration attempts. Please try again later.'
            ]);
        }
        
        $result = Auth::register($email, $username);
        
        if ($result['success']) {
            return $this->render('auth/register', ['success' => 'Check your email to complete registration']);
        }
        
        return $this->render('auth/register', ['error' => $result['error']]);
    }
    
    public function verify(array $params): string
    {
        $token = $this->request->get('token', '');
        
        if (empty($token)) {
            return $this->render('auth/verify', ['error' => 'Invalid token']);
        }
        
        $result = Auth::verifyToken($token);
        
        if ($result['success']) {
            $this->redirect('/');
        }
        
        return $this->render('auth/verify', ['error' => $result['error']]);
    }
    
    public function logout(array $params): void
    {
        Auth::logout();
        $this->redirect('/');
    }
}