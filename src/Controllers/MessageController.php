<?php

namespace Quidque\Controllers;

use Quidque\Core\Auth;
use Quidque\Models\Message;
use Quidque\Helpers\RateLimiter;
use Quidque\Helpers\FileValidator;
use Quidque\Helpers\Str;
use Quidque\Constants;

class MessageController extends Controller
{
    private const MAX_MESSAGES_PER_MINUTE = 5;
    private const MESSAGE_DECAY_SECONDS = 60;
    
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
        
        $userId = Auth::id();
        $rateLimitKey = 'message:' . $userId;
        
        if (!RateLimiter::attempt($rateLimitKey, self::MAX_MESSAGES_PER_MINUTE, self::MESSAGE_DECAY_SECONDS)) {
            return $this->json(['error' => 'Please wait before sending another message'], 429);
        }
        
        if (!empty($_FILES['image']['name'])) {
            $upload = $this->handleImageUpload();
            if (isset($upload['error'])) {
                return $this->json(['error' => $upload['error']], 400);
            }
            $imagePath = $upload['path'];
        }
        
        Message::sendFromUser($userId, $content, $imagePath);
        
        if ($this->request->isHtmx()) {
            $messages = Message::getConversation($userId);
            return $this->render('partials/messages', [
                'messages' => $messages,
            ]);
        }
        
        return $this->json(['success' => true]);
    }
    
    private function handleImageUpload(): array
    {
        $validation = FileValidator::validate($_FILES['image'], [
            'max_size' => $this->config['uploads']['max_size'] ?? Constants::MAX_UPLOAD_SIZE,
            'allowed_types' => $this->config['uploads']['allowed_types'] ?? Constants::ALLOWED_IMAGE_TYPES,
            'require_image' => true,
        ]);
        
        if (!$validation['valid']) {
            return ['error' => $validation['error']];
        }
        
        $filename = Str::random(32) . '.' . $validation['extension'];
        $path = 'messages/' . date('Y/m/');
        $fullPath = BASE_PATH . '/storage/uploads/' . $path;
        
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0775, true);
        }
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $fullPath . $filename)) {
            return ['error' => 'Failed to save file'];
        }
        
        return ['path' => $path . $filename];
    }
}