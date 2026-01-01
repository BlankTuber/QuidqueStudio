<?php

namespace Quidque;

class Constants
{
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
    
    public const PROJECT_STATUS_LABELS = [
        self::PROJECT_ACTIVE => 'Active',
        self::PROJECT_COMPLETE => 'Complete',
        self::PROJECT_ON_HOLD => 'On Hold',
        self::PROJECT_ARCHIVED => 'Archived',
    ];
    
    public const PROJECT_STATUS_COLORS = [
        self::PROJECT_ACTIVE => 'cyan',
        self::PROJECT_COMPLETE => 'green',
        self::PROJECT_ON_HOLD => 'magenta',
        self::PROJECT_ARCHIVED => 'gray',
    ];
    
    public const POST_DRAFT = 'draft';
    public const POST_PUBLISHED = 'published';
    
    public const POST_STATUSES = [
        self::POST_DRAFT,
        self::POST_PUBLISHED,
    ];
    
    public const MEDIA_IMAGE = 'image';
    public const MEDIA_VIDEO = 'video';
    public const MEDIA_AUDIO = 'audio';
    
    public const MEDIA_TYPES = [
        self::MEDIA_IMAGE,
        self::MEDIA_VIDEO,
        self::MEDIA_AUDIO,
    ];
    
    public const DELETED_BY_USER = 'user';
    public const DELETED_BY_ADMIN = 'admin';
    
    public const TIER_CORE = 1;
    public const TIER_FRAMEWORK = 2;
    public const TIER_LIBRARY = 3;
    public const TIER_TOOL = 4;
    
    public const MAX_PROJECT_TAGS = 2;
    public const MAX_COMMENT_LENGTH = 2000;
    public const MAX_UPLOAD_SIZE = 10 * 1024 * 1024;
    
    public const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];
    
    public const ALLOWED_VIDEO_TYPES = [
        'video/mp4',
        'video/webm',
    ];
    
    public const ALLOWED_AUDIO_TYPES = [
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',
    ];
    
    public const ALLOWED_MEDIA_TYPES = [
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
    
    public const FILE_SIGNATURES = [
        'image/jpeg' => ["\xFF\xD8\xFF"],
        'image/png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
        'image/gif' => ["GIF87a", "GIF89a"],
        'image/webp' => ["RIFF"],
        'video/mp4' => ["\x00\x00\x00\x18ftypmp4", "\x00\x00\x00\x1Cftypisom", "\x00\x00\x00"],
        'video/webm' => ["\x1A\x45\xDF\xA3"],
        'audio/mpeg' => ["\xFF\xFB", "\xFF\xFA", "\xFF\xF3", "\xFF\xF2", "ID3"],
        'audio/wav' => ["RIFF"],
        'audio/ogg' => ["OggS"],
    ];
}