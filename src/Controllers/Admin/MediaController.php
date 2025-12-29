<?php

namespace Quidque\Controllers\Admin;

use Quidque\Controllers\Controller;
use Quidque\Core\Auth;
use Quidque\Models\Media;

class MediaController extends Controller
{
    public function index(array $params): string
    {
        $type = $this->request->get('type');
        
        if ($type && in_array($type, ['image', 'video', 'audio'])) {
            $media = Media::getByType($type);
        } else {
            $media = Media::all('created_at', 'DESC');
        }
        
        return $this->render('admin/media/index', [
            'media' => $media,
            'currentType' => $type,
        ]);
    }
    
    public function upload(array $params): string
    {
        if (empty($_FILES['file']['name'])) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }
        
        $file = $_FILES['file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'Upload failed'], 400);
        }
        
        $mimeType = mime_content_type($file['tmp_name']);
        $fileType = $this->getFileType($mimeType);
        
        if (!$fileType) {
            return $this->json(['error' => 'Invalid file type'], 400);
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $path = 'media/' . date('Y/m/');
        $fullPath = BASE_PATH . '/storage/uploads/' . $path;
        
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0775, true);
        }
        
        if (!move_uploaded_file($file['tmp_name'], $fullPath . $filename)) {
            return $this->json(['error' => 'Failed to save file'], 500);
        }
        
        $altText = trim($this->request->post('alt_text', ''));
        
        $id = Media::upload(
            $path . $filename,
            $fileType,
            $mimeType,
            $file['size'],
            Auth::id(),
            $altText ?: null
        );
        
        if ($this->request->isHtmx()) {
            $media = Media::all('created_at', 'DESC');
            return $this->render('admin/partials/media-grid', ['media' => $media]);
        }
        
        return $this->json(['success' => true, 'id' => $id]);
    }
    
    public function update(array $params): string
    {
        $media = Media::find((int) $params['id']);
        
        if (!$media) {
            return $this->json(['error' => 'Media not found'], 404);
        }
        
        $altText = trim($this->request->post('alt_text', ''));
        
        Media::update($media['id'], ['alt_text' => $altText ?: null]);
        
        return $this->json(['success' => true]);
    }
    
    public function delete(array $params): string
    {
        $media = Media::find((int) $params['id']);
        
        if (!$media) {
            return $this->json(['error' => 'Media not found'], 404);
        }
        
        $filePath = BASE_PATH . '/storage/uploads/' . $media['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        Media::delete($media['id']);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        return $this->json(['success' => true]);
    }
    
    private function getFileType(string $mimeType): ?string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        return null;
    }
}