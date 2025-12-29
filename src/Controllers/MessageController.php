<?php

namespace Quidque\Controllers;

use Quidque\Core\Auth;
use Quidque\Models\Message;
use Quidque\Models\User;

class MessageController extends Controller
{
    public function index(array $params): string
    {
        $messages = Message::getConversation(Auth::id());
        
        return $this->render('messages/index', [
            'messages' => $messages,
        ]);
    }
    
    public function send(array $params): string
    {
        $content = trim($this->request->post('content', ''));
        $imagePath = null;
        
        if (empty($content) && empty($_FILES['image']['name'])) {
            return $this->json(['error' => 'Message cannot be empty'], 400);
        }
        
        if (!empty($_FILES['image']['name'])) {
            $upload = $this->handleImageUpload();
            if (isset($upload['error'])) {
                return $this->json(['error' => $upload['error']], 400);
            }
            $imagePath = $upload['path'];
        }
        
        Message::sendFromUser(Auth::id(), $content, $imagePath);
        
        if ($this->request->isHtmx()) {
            $messages = Message::getConversation(Auth::id());
            return $this->render('partials/messages', [
                'messages' => $messages,
            ]);
        }
        
        return $this->json(['success' => true]);
    }
    
    private function handleImageUpload(): array
    {
        $file = $_FILES['image'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload failed'];
        }
        
        if ($file['size'] > $this->config['uploads']['max_size']) {
            return ['error' => 'File too large (max 2MB)'];
        }
        
        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, $this->config['uploads']['allowed_types'])) {
            return ['error' => 'Invalid file type'];
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $path = 'messages/' . date('Y/m/');
        $fullPath = BASE_PATH . '/storage/uploads/' . $path;
        
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0775, true);
        }
        
        if (!move_uploaded_file($file['tmp_name'], $fullPath . $filename)) {
            return ['error' => 'Failed to save file'];
        }
        
        return ['path' => $path . $filename];
    }
}