<?php

namespace Quidque\Helpers;

use Quidque\Constants;

class FileValidator
{
    public static function validate(array $file, array $options = []): array
    {
        $maxSize = $options['max_size'] ?? Constants::MAX_UPLOAD_SIZE;
        $allowedTypes = $options['allowed_types'] ?? Constants::ALLOWED_IMAGE_TYPES;
        $requireImage = $options['require_image'] ?? false;
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => self::getUploadError($file['error'])];
        }
        
        if ($file['size'] > $maxSize) {
            $maxMB = round($maxSize / (1024 * 1024), 1);
            return ['valid' => false, 'error' => "File too large (max {$maxMB}MB)"];
        }
        
        $mimeType = self::detectMimeType($file['tmp_name']);
        
        if ($mimeType === null) {
            return ['valid' => false, 'error' => 'Could not determine file type'];
        }
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['valid' => false, 'error' => 'File type not allowed'];
        }
        
        if (!self::verifySignature($file['tmp_name'], $mimeType)) {
            return ['valid' => false, 'error' => 'File content does not match type'];
        }
        
        $extension = self::getExtensionForMime($mimeType);
        if ($extension === null) {
            return ['valid' => false, 'error' => 'Unknown file extension'];
        }
        
        $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $validExtensions = self::getValidExtensions($mimeType);
        if (!in_array($originalExt, $validExtensions)) {
            return ['valid' => false, 'error' => 'File extension does not match content'];
        }
        
        if ($requireImage || str_starts_with($mimeType, 'image/')) {
            if (str_starts_with($mimeType, 'image/') && !self::isValidImage($file['tmp_name'])) {
                return ['valid' => false, 'error' => 'Invalid or corrupted image'];
            }
        }
        
        return [
            'valid' => true,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'file_type' => self::getFileType($mimeType),
            'size' => $file['size'],
        ];
    }
    
    public static function detectMimeType(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $path);
        finfo_close($finfo);
        
        return $mimeType ?: null;
    }
    
    public static function verifySignature(string $path, string $expectedMime): bool
    {
        if (!isset(Constants::FILE_SIGNATURES[$expectedMime])) {
            return true;
        }
        
        $handle = fopen($path, 'rb');
        if (!$handle) {
            return false;
        }
        
        $header = fread($handle, 32);
        fclose($handle);
        
        foreach (Constants::FILE_SIGNATURES[$expectedMime] as $signature) {
            if (str_starts_with($header, $signature)) {
                return true;
            }
        }
        
        if ($expectedMime === 'image/webp' && strlen($header) >= 12) {
            if (substr($header, 8, 4) === 'WEBP') {
                return true;
            }
        }
        
        if ($expectedMime === 'audio/wav' && strlen($header) >= 12) {
            if (str_starts_with($header, 'RIFF') && substr($header, 8, 4) === 'WAVE') {
                return true;
            }
        }
        
        if ($expectedMime === 'video/mp4' && strlen($header) >= 8) {
            if (strpos($header, 'ftyp') !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function isValidImage(string $path): bool
    {
        $info = @getimagesize($path);
        
        if ($info === false) {
            return false;
        }
        
        if ($info[0] <= 0 || $info[1] <= 0) {
            return false;
        }
        
        return true;
    }
    
    public static function getFileType(string $mimeType): ?string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return Constants::MEDIA_IMAGE;
        }
        if (str_starts_with($mimeType, 'video/')) {
            return Constants::MEDIA_VIDEO;
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return Constants::MEDIA_AUDIO;
        }
        return null;
    }
    
    public static function getExtensionForMime(string $mimeType): ?string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'audio/ogg' => 'ogg',
        ];
        
        return $map[$mimeType] ?? null;
    }
    
    public static function getValidExtensions(string $mimeType): array
    {
        $map = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp'],
            'video/mp4' => ['mp4', 'm4v'],
            'video/webm' => ['webm'],
            'audio/mpeg' => ['mp3'],
            'audio/wav' => ['wav'],
            'audio/ogg' => ['ogg', 'oga'],
        ];
        
        return $map[$mimeType] ?? [];
    }
    
    private static function getUploadError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by server extension',
            default => 'Unknown upload error',
        };
    }
    
    public static function getImageDimensions(string $path): ?array
    {
        $info = @getimagesize($path);
        
        if ($info === false) {
            return null;
        }
        
        return [
            'width' => $info[0],
            'height' => $info[1],
            'type' => $info[2],
            'mime' => $info['mime'],
        ];
    }
}