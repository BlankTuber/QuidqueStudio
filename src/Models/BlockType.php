<?php

namespace Quidque\Models;

class BlockType extends Model
{
    protected static string $table = 'block_types';
    
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