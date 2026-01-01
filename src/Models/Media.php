<?php

namespace Quidque\Models;

use Quidque\Constants;

class Media extends Model
{
    protected static string $table = 'media';
    
    public static function upload(string $filePath, string $fileType, string $mimeType, int $fileSize, ?int $uploadedBy = null, ?string $altText = null): int
    {
        return self::create([
            'file_path' => $filePath,
            'file_type' => $fileType,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'alt_text' => $altText,
            'uploaded_by' => $uploadedBy,
        ]);
    }
    
    public static function getByType(string $type): array
    {
        if (!in_array($type, Constants::MEDIA_TYPES)) {
            return [];
        }
        return self::where('file_type', $type, 'created_at DESC');
    }
    
    public static function images(): array
    {
        return self::getByType(Constants::MEDIA_IMAGE);
    }
    
    public static function videos(): array
    {
        return self::getByType(Constants::MEDIA_VIDEO);
    }
    
    public static function audio(): array
    {
        return self::getByType(Constants::MEDIA_AUDIO);
    }
    
    public static function isInUse(int $mediaId): bool
    {
        $galleryUse = self::$db->fetch(
            "SELECT 1 FROM gallery_items WHERE media_id = ? LIMIT 1",
            [$mediaId]
        );
        
        if ($galleryUse) {
            return true;
        }
        
        $blogBlockUse = self::$db->fetch(
            "SELECT 1 FROM blog_blocks WHERE data LIKE ? LIMIT 1",
            ['%"media_id":' . $mediaId . '%']
        );
        
        if ($blogBlockUse) {
            return true;
        }
        
        $blogBlockUse2 = self::$db->fetch(
            "SELECT 1 FROM blog_blocks WHERE data LIKE ? LIMIT 1",
            ['%"media_id":"' . $mediaId . '"%']
        );
        
        return $blogBlockUse2 !== null;
    }
    
    public static function getUsageCount(int $mediaId): int
    {
        $count = 0;
        
        $galleryCount = self::$db->fetch(
            "SELECT COUNT(*) as count FROM gallery_items WHERE media_id = ?",
            [$mediaId]
        );
        $count += (int) $galleryCount['count'];
        
        return $count;
    }
    
    public static function getPaginated(int $page, int $perPage = 24, ?string $type = null): array
    {
        $where = '1=1';
        $params = [];
        
        if ($type !== null && in_array($type, Constants::MEDIA_TYPES)) {
            $where = 'file_type = ?';
            $params[] = $type;
        }
        
        return self::paginate($page, $perPage, 'created_at', 'DESC', $where, $params);
    }
    
    public static function getTotalSize(): int
    {
        $result = self::$db->fetch(
            "SELECT SUM(file_size) as total FROM " . static::$table
        );
        return (int) ($result['total'] ?? 0);
    }
    
    public static function getUrl(array $media): string
    {
        return '/uploads/' . $media['file_path'];
    }
}