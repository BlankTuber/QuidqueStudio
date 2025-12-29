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
        
        return $this->render('admin/messages/index', [
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
        
        return $this->render('admin/messages/show', [
            'user' => $user,
            'messages' => $messages,
        ]);
    }
    
    public function reply(array $params): string
    {
        $user = User::find((int) $params['id']);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        
        $content = trim($this->request->post('content', ''));
        
        if (empty($content)) {
            return $this->json(['error' => 'Message cannot be empty'], 400);
        }
        
        Message::sendFromAdmin($user['id'], $content);
        
        if ($this->request->isHtmx()) {
            $messages = Message::getConversation($user['id']);
            return $this->render('admin/partials/message-thread', [
                'messages' => $messages,
                'user' => $user,
            ]);
        }
        
        $this->redirect('/admin/messages/' . $user['id']);
    }
}