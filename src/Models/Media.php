<?php

namespace Quidque\Models;

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
        return self::where('file_type', $type, 'created_at DESC');
    }
    
    public static function images(): array
    {
        return self::getByType('image');
    }
    
    public static function videos(): array
    {
        return self::getByType('video');
    }
    
    public static function audio(): array
    {
        return self::getByType('audio');
    }
}