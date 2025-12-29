<?php

namespace Quidque\Models;

class BlogBlockType extends Model
{
    protected static string $table = 'blog_block_types';
    
    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }
    
    public static function getSchema(int $id): array
    {
        $type = self::find($id);
        if (!$type || !$type['schema']) {
            return [];
        }
        return json_decode($type['schema'], true) ?? [];
    }
}