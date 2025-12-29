<?php

namespace Quidque\Controllers;

use Quidque\Core\Auth;
use Quidque\Models\User;
use Quidque\Models\Session;
use Quidque\Models\Comment;
use Quidque\Models\Message;

class UserController extends Controller
{
    public function settings(array $params): string
    {
        $user = User::find(Auth::id());
        $settings = User::getSettings(Auth::id());
        
        return $this->render('user/settings', [
            'user' => $user,
            'settings' => $settings,
        ]);
    }
    
    public function updateSettings(array $params): string
    {
        $username = trim($this->request->post('username', ''));
        
        if (!empty($username)) {
            if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
                return $this->render('user/settings', [
                    'user' => User::find(Auth::id()),
                    'settings' => User::getSettings(Auth::id()),
                    'error' => 'Invalid username format',
                ]);
            }
            
            $existing = User::findByUsername($username);
            if ($existing && $existing['id'] !== Auth::id()) {
                return $this->render('user/settings', [
                    'user' => User::find(Auth::id()),
                    'settings' => User::getSettings(Auth::id()),
                    'error' => 'Username already taken',
                ]);
            }
            
            User::update(Auth::id(), ['username' => $username]);
        }
        
        $settings = [
            'anonymous' => $this->request->post('anonymous') === '1',
            'theme' => $this->request->post('theme', 'dark'),
        ];
        
        User::updateSettings(Auth::id(), $settings);
        
        $this->redirect('/settings?saved=1');
    }
    
    public function sessions(array $params): string
    {
        $sessions = Session::getUserSessions(Auth::id());
        $currentSession = $_COOKIE[$this->config['session']['cookie_name']] ?? null;
        
        return $this->render('user/sessions', [
            'sessions' => $sessions,
            'currentSession' => $currentSession,
        ]);
    }
    
    public function destroySession(array $params): string
    {
        $sessionId = $params['id'];
        $currentSession = $_COOKIE[$this->config['session']['cookie_name']] ?? null;
        
        // Verify session belongs to user
        $session = Session::findValid($sessionId);
        if (!$session || $session['user_id'] !== Auth::id()) {
            return $this->json(['error' => 'Session not found'], 404);
        }
        
        Session::destroy($sessionId);
        
        // If destroying current session, redirect to login
        if ($sessionId === $currentSession) {
            Auth::logout();
            $this->redirect('/login');
        }
        
        $this->redirect('/settings/sessions');
    }
    
    public function destroyAllSessions(array $params): void
    {
        Auth::logoutAll();
        $this->redirect('/login');
    }
    
    public function deleteAccount(array $params): string
    {
        return $this->render('user/delete-confirm');
    }
    
    public function confirmDeleteAccount(array $params): void
    {
        $confirm = $this->request->post('confirm');
        
        if ($confirm !== 'DELETE') {
            $this->redirect('/settings/delete');
            return;
        }
        
        $userId = Auth::id();
        
        // Logout first
        Auth::logoutAll();
        
        // Delete user (cascades to comments, messages, sessions, tokens)
        User::delete($userId);
        
        $this->redirect('/');
    }
}