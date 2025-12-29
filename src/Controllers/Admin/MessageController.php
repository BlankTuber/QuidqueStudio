<?php

namespace Quidque\Controllers\Admin;

use Quidque\Controllers\Controller;
use Quidque\Models\Message;
use Quidque\Models\User;

class MessageController extends Controller
{
    public function index(array $params): string
    {
        $conversations = Message::getAllConversations();
        
        return $this->renderAdmin('admin/messages/index', [
            'conversations' => $conversations,
        ]);
    }
    
    public function show(array $params): string
    {
        $user = User::find((int) $params['id']);
        
        if (!$user) {
            return $this->notFound();
        }
        
        $messages = Message::getConversation($user['id']);
        
        return $this->renderAdmin('admin/messages/show', [
            'user' => $user,
            'messages' => $messages,
        ]);
    }
    
    public function reply(array $params): string
    {
        $user = User::find((int) $params['id']);
        
        if (!$user) {
            $this->redirect('/admin/messages?error=User+not+found');
            return '';
        }
        
        $content = trim($this->request->post('content', ''));
        
        if (empty($content)) {
            $this->redirect('/admin/messages/' . $user['id'] . '?error=Message+cannot+be+empty');
            return '';
        }
        
        Message::sendFromAdmin($user['id'], $content);
        
        $this->redirect('/admin/messages/' . $user['id']);
        return '';
    }
}