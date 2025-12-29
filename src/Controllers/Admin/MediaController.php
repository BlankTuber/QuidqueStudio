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
        
        return $this->renderAdmin('admin/media/index', [
            'media' => $media,
            'currentType' => $type,
        ]);
    }
    
    public function upload(array $params): string
    {
        if (empty($_FILES['file']['name'])) {
            $this->redirect('/admin/media?error=No+file+uploaded');
            return '';
        }
        
        $file = $_FILES['file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
                UPLOAD_ERR_FORM_SIZE => 'File too large',
                UPLOAD_ERR_PARTIAL => 'Upload incomplete',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
            ];
            $error = $errors[$file['error']] ?? 'Upload failed';
            $this->redirect('/admin/media?error=' . urlencode($error));
            return '';
        }
        
        $mimeType = mime_content_type($file['tmp_name']);
        $fileType = $this->getFileType($mimeType);
        
        if (!$fileType) {
            $this->redirect('/admin/media?error=Invalid+file+type');
            return '';
        }
        
        // Check file size (10MB max)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $this->redirect('/admin/media?error=File+too+large+(max+10MB)');
            return '';
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $path = 'media/' . date('Y/m/');
        $fullPath = BASE_PATH . '/storage/uploads/' . $path;
        
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0775, true);
        }
        
        if (!move_uploaded_file($file['tmp_name'], $fullPath . $filename)) {
            $this->redirect('/admin/media?error=Failed+to+save+file');
            return '';
        }
        
        $altText = trim($this->request->post('alt_text', ''));
        
        Media::upload(
            $path . $filename,
            $fileType,
            $mimeType,
            $file['size'],
            Auth::id(),
            $altText ?: null
        );
        
        $this->redirect('/admin/media?saved=1');
        return '';
    }
    
    public function update(array $params): string
    {
        $media = Media::find((int) $params['id']);
        
        if (!$media) {
            $this->redirect('/admin/media?error=Media+not+found');
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
            $this->redirect('/admin/media?error=Media+not+found');
            return '';
        }
        
        // Delete file
        $filePath = BASE_PATH . '/storage/uploads/' . $media['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        Media::delete($media['id']);
        
        $this->redirect('/admin/media?deleted=1');
        return '';
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