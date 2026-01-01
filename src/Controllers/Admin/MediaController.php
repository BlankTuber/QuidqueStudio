<?php

namespace Quidque\Controllers\Admin;

use Quidque\Controllers\Controller;
use Quidque\Core\Auth;
use Quidque\Models\Media;
use Quidque\Helpers\FileValidator;
use Quidque\Helpers\Str;
use Quidque\Constants;

class MediaController extends Controller
{
    private const ALLOWED_UPLOAD_TYPES = [
        'image/jpeg',
        'image/png', 
        'image/gif',
        'image/webp',
        'video/mp4',
        'video/webm',
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',
    ];
    
    public function index(array $params): string
    {
        $type = $this->request->get('type');
        
        if ($type && in_array($type, Constants::MEDIA_TYPES)) {
            $media = Media::getByType($type);
        } else {
            $media = Media::all('created_at', 'DESC');
        }
        
        return $this->renderAdmin('admin/media/index', [
            'media' => $media,
            'currentType' => $type,
        ]);
    }
    
    public function upload(array $params): string
    {
        if (empty($_FILES['file']['name'])) {
            $this->redirect('/admin/media?error=' . urlencode('No file uploaded'));
            return '';
        }
        
        $validation = FileValidator::validate($_FILES['file'], [
            'max_size' => $this->config['uploads']['max_size'] ?? Constants::MAX_UPLOAD_SIZE,
            'allowed_types' => self::ALLOWED_UPLOAD_TYPES,
        ]);
        
        if (!$validation['valid']) {
            $this->redirect('/admin/media?error=' . urlencode($validation['error']));
            return '';
        }
        
        $filename = Str::random(32) . '.' . $validation['extension'];
        $path = 'media/' . date('Y/m/');
        $fullPath = BASE_PATH . '/storage/uploads/' . $path;
        
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0775, true);
        }
        
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $fullPath . $filename)) {
            $this->redirect('/admin/media?error=' . urlencode('Failed to save file'));
            return '';
        }
        
        $altText = trim($this->request->post('alt_text', ''));
        
        Media::upload(
            $path . $filename,
            $validation['file_type'],
            $validation['mime_type'],
            $validation['size'],
            Auth::id(),
            $altText ?: null
        );
        
        $this->redirect('/admin/media?saved=1');
        return '';
    }
    
    public function uploadAjax(array $params): string
    {
        if (empty($_FILES['file']['name'])) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }
        
        $validation = FileValidator::validate($_FILES['file'], [
            'max_size' => $this->config['uploads']['max_size'] ?? Constants::MAX_UPLOAD_SIZE,
            'allowed_types' => Constants::ALLOWED_IMAGE_TYPES,
            'require_image' => true,
        ]);
        
        if (!$validation['valid']) {
            return $this->json(['error' => $validation['error']], 400);
        }
        
        $filename = Str::random(32) . '.' . $validation['extension'];
        $path = 'media/' . date('Y/m/');
        $fullPath = BASE_PATH . '/storage/uploads/' . $path;
        
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0775, true);
        }
        
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $fullPath . $filename)) {
            return $this->json(['error' => 'Failed to save file'], 500);
        }
        
        $altText = trim($this->request->post('alt_text', ''));
        
        $mediaId = Media::upload(
            $path . $filename,
            $validation['file_type'],
            $validation['mime_type'],
            $validation['size'],
            Auth::id(),
            $altText ?: null
        );
        
        $media = Media::find($mediaId);
        
        return $this->json([
            'success' => true,
            'media' => [
                'id' => $media['id'],
                'file_path' => $media['file_path'],
                'alt_text' => $media['alt_text'],
                'url' => '/uploads/' . $media['file_path'],
            ]
        ]);
    }
    
    public function update(array $params): string
    {
        $media = Media::find((int) $params['id']);
        
        if (!$media) {
            $this->redirect('/admin/media?error=' . urlencode('Media not found'));
            return '';
        }
        
        $altText = trim($this->request->post('alt_text', ''));
        
        Media::update($media['id'], ['alt_text' => $altText ?: null]);
        
        $this->redirect('/admin/media?saved=1');
        return '';
    }
    
    public function delete(array $params): string
    {
        $media = Media::find((int) $params['id']);
        
        if (!$media) {
            if ($this->request->isHtmx()) {
                return $this->json(['error' => 'Media not found'], 404);
            }
            $this->redirect('/admin/media?error=' . urlencode('Media not found'));
            return '';
        }
        
        if (Media::isInUse($media['id'])) {
            if ($this->request->isHtmx()) {
                return $this->json(['error' => 'Media is in use and cannot be deleted'], 400);
            }
            $this->redirect('/admin/media?error=' . urlencode('Media is in use and cannot be deleted'));
            return '';
        }
        
        $filePath = BASE_PATH . '/storage/uploads/' . $media['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        Media::delete($media['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        $this->redirect('/admin/media?deleted=1');
        return '';
    }
}