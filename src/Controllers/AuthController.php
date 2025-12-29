<?php

namespace Quidque\Controllers;

use Quidque\Core\Auth;

class AuthController extends Controller
{
    public function loginForm(array $params): string
    {
        return $this->render('auth/login');
    }
    
    public function login(array $params): string
    {
        $email = trim($this->request->post('email', ''));
        
        if (empty($email)) {
            return $this->render('auth/login', ['error' => 'Email is required']);
        }
        
        $result = Auth::requestLogin($email);
        
        if ($result['success']) {
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
        
        if (empty($email) || empty($username)) {
            return $this->render('auth/register', ['error' => 'All fields are required']);
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