<?php

namespace Quidque\Controllers\Admin;

use Quidque\Controllers\Controller;
use Quidque\Core\Auth;
use Quidque\Models\User;
use Quidque\Models\Session;
use Quidque\Models\Comment;

class UserController extends Controller
{
    public function index(array $params): string
    {
        $users = User::all('created_at', 'DESC');
        
        return $this->render('admin/users/index', [
            'users' => $users,
        ]);
    }
    
    public function show(array $params): string
    {
        $user = User::find((int) $params['id']);
        
        if (!$user) {
            return $this->notFound();
        }
        
        $sessions = Session::getUserSessions($user['id']);
        $comments = Comment::getRecentByUser($user['id'], 20);
        
        return $this->render('admin/users/show', [
            'user' => $user,
            'sessions' => $sessions,
            'comments' => $comments,
        ]);
    }
    
    public function toggleAdmin(array $params): string
    {
        $user = User::find((int) $params['id']);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        
        if ($user['id'] === Auth::id()) {
            return $this->json(['error' => 'Cannot modify your own admin status'], 400);
        }
        
        User::update($user['id'], ['is_admin' => $user['is_admin'] ? 0 : 1]);
        
        $this->redirect('/admin/users/' . $user['id']);
    }
    
    public function delete(array $params): string
    {
        $user = User::find((int) $params['id']);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        
        if ($user['id'] === Auth::id()) {
            return $this->json(['error' => 'Cannot delete yourself'], 400);
        }
        
        Session::destroyAllForUser($user['id']);
        User::delete($user['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        $this->redirect('/admin/users?deleted=1');
    }
    
    public function destroySession(array $params): string
    {
        $session = Session::findValid($params['sessionId']);
        
        if (!$session) {
            return $this->json(['error' => 'Session not found'], 404);
        }
        
        Session::destroy($params['sessionId']);
        
        $this->redirect('/admin/users/' . $session['user_id']);
    }
}