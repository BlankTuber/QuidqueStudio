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
        
        return $this->renderAdmin('admin/users/index', [
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
        
        return $this->renderAdmin('admin/users/show', [
            'user' => $user,
            'sessions' => $sessions,
            'comments' => $comments,
        ]);
    }
    
    public function toggleAdmin(array $params): string
    {
        $user = User::find((int) $params['id']);
        
        if (!$user) {
            $this->redirect('/admin/users?error=User+not+found');
            return '';
        }
        
        if ($user['id'] === Auth::id()) {
            $this->redirect('/admin/users/' . $user['id'] . '?error=Cannot+modify+your+own+admin+status');
            return '';
        }
        
        User::update($user['id'], ['is_admin' => $user['is_admin'] ? 0 : 1]);
        
        $this->redirect('/admin/users/' . $user['id'] . '?saved=1');
        return '';
    }
    
    public function delete(array $params): string
    {
        $user = User::find((int) $params['id']);
        
        if (!$user) {
            $this->redirect('/admin/users?error=User+not+found');
            return '';
        }
        
        if ($user['id'] === Auth::id()) {
            $this->redirect('/admin/users/' . $user['id'] . '?error=Cannot+delete+yourself');
            return '';
        }
        
        Session::destroyAllForUser($user['id']);
        User::delete($user['id']);
        
        $this->redirect('/admin/users?deleted=1');
        return '';
    }
    
    public function destroySession(array $params): string
    {
        $session = Session::findValid($params['sessionId']);
        
        if (!$session) {
            $this->redirect('/admin/users?error=Session+not+found');
            return '';
        }
        
        $userId = $session['user_id'];
        Session::destroy($params['sessionId']);
        
        $this->redirect('/admin/users/' . $userId . '?saved=1');
        return '';
    }
}