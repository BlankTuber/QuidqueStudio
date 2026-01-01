<?php

namespace Quidque;

class Constants
{
    // Project statuses
    public const PROJECT_ACTIVE = 'active';
    public const PROJECT_COMPLETE = 'complete';
    public const PROJECT_ON_HOLD = 'on_hold';
    public const PROJECT_ARCHIVED = 'archived';
    
    public const PROJECT_STATUSES = [
        self::PROJECT_ACTIVE,
        self::PROJECT_COMPLETE,
        self::PROJECT_ON_HOLD,
        self::PROJECT_ARCHIVED,
    ];
    
    // Blog post statuses
    public const POST_DRAFT = 'draft';
    public const POST_PUBLISHED = 'published';
    
    public const POST_STATUSES = [
        self::POST_DRAFT,
        self::POST_PUBLISHED,
    ];
    
    // Media types
    public const MEDIA_IMAGE = 'image';
    public const MEDIA_VIDEO = 'video';
    public const MEDIA_AUDIO = 'audio';
    
    public const MEDIA_TYPES = [
        self::MEDIA_IMAGE,
        self::MEDIA_VIDEO,
        self::MEDIA_AUDIO,
    ];
    
    // Comment deletion types
    public const DELETED_BY_USER = 'user';
    public const DELETED_BY_ADMIN = 'admin';
    
    // Tech stack tiers
    public const TIER_CORE = 1;
    public const TIER_FRAMEWORK = 2;
    public const TIER_LIBRARY = 3;
    public const TIER_TOOL = 4;
    
    // Limits
    public const MAX_PROJECT_TAGS = 2;
    public const MAX_COMMENT_LENGTH = 2000;
    public const MAX_UPLOAD_SIZE = 10 * 1024 * 1024; // 10MB
    
    // Allowed upload MIME types
    public const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];
    
    // File signatures (magic bytes) for validation
    public const FILE_SIGNATURES = [
        'image/jpeg' => ["\xFF\xD8\xFF"],
        'image/png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
        'image/gif' => ["GIF87a", "GIF89a"],
        'image/webp' => ["RIFF"],
    ];
}