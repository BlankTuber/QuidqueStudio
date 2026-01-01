<?php

namespace Quidque\Controllers;

use Quidque\Core\Auth;
use Quidque\Helpers\RateLimiter;
use Quidque\Helpers\Seo;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_DECAY_SECONDS = 900;
    private const MAX_VERIFY_ATTEMPTS = 10;
    private const VERIFY_DECAY_SECONDS = 300;
    
    public function loginForm(array $params): string
    {
        return $this->render('auth/login', [
            'seo' => Seo::noIndex(),
        ]);
    }
    
    public function login(array $params): string
    {
        $email = trim($this->request->post('email', ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if (empty($email)) {
            return $this->render('auth/login', [
                'error' => 'Email is required',
                'seo' => Seo::noIndex(),
            ]);
        }
        
        $rateLimitKey = 'login:' . $ip;
        
        if (!RateLimiter::attempt($rateLimitKey, self::MAX_LOGIN_ATTEMPTS, self::LOGIN_DECAY_SECONDS)) {
            $waitTime = RateLimiter::availableIn($rateLimitKey, self::LOGIN_DECAY_SECONDS);
            $minutes = ceil($waitTime / 60);
            return $this->render('auth/login', [
                'error' => "Too many login attempts. Please try again in {$minutes} minute(s).",
                'seo' => Seo::noIndex(),
            ]);
        }
        
        $result = Auth::requestLogin($email);
        
        if ($result['success']) {
            RateLimiter::clear($rateLimitKey);
            return $this->render('auth/login', [
                'success' => 'Check your email for a login link',
                'seo' => Seo::noIndex(),
            ]);
        }
        
        return $this->render('auth/login', [
            'error' => $result['error'],
            'seo' => Seo::noIndex(),
        ]);
    }
    
    public function registerForm(array $params): string
    {
        return $this->render('auth/register', [
            'seo' => Seo::noIndex(),
        ]);
    }
    
    public function register(array $params): string
    {
        $email = trim($this->request->post('email', ''));
        $username = trim($this->request->post('username', ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if (empty($email) || empty($username)) {
            return $this->render('auth/register', [
                'error' => 'All fields are required',
                'seo' => Seo::noIndex(),
            ]);
        }
        
        $rateLimitKey = 'register:' . $ip;
        
        if (!RateLimiter::attempt($rateLimitKey, 3, 3600)) {
            return $this->render('auth/register', [
                'error' => 'Too many registration attempts. Please try again later.',
                'seo' => Seo::noIndex(),
            ]);
        }
        
        $result = Auth::register($email, $username);
        
        if ($result['success']) {
            return $this->render('auth/register', [
                'success' => 'Check your email to complete registration',
                'seo' => Seo::noIndex(),
            ]);
        }
        
        return $this->render('auth/register', [
            'error' => $result['error'],
            'seo' => Seo::noIndex(),
        ]);
    }
    
    public function verify(array $params): string
    {
        $token = $this->request->get('token', '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if (empty($token)) {
            return $this->render('auth/verify', [
                'error' => 'Invalid token',
                'seo' => Seo::noIndex(),
            ]);
        }
        
        $rateLimitKey = 'verify:' . $ip;
        
        if (!RateLimiter::attempt($rateLimitKey, self::MAX_VERIFY_ATTEMPTS, self::VERIFY_DECAY_SECONDS)) {
            $waitTime = RateLimiter::availableIn($rateLimitKey, self::VERIFY_DECAY_SECONDS);
            $minutes = ceil($waitTime / 60);
            return $this->render('auth/verify', [
                'error' => "Too many verification attempts. Please try again in {$minutes} minute(s).",
                'seo' => Seo::noIndex(),
            ]);
        }
        
        $result = Auth::verifyToken($token);
        
        if ($result['success']) {
            RateLimiter::clear($rateLimitKey);
            $this->redirect('/');
        }
        
        return $this->render('auth/verify', [
            'error' => $result['error'],
            'seo' => Seo::noIndex(),
        ]);
    }
    
    public function logout(array $params): void
    {
        Auth::logout();
        $this->redirect('/');
    }
}