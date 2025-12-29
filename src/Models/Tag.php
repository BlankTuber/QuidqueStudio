<?php

namespace Quidque\Models;

class Tag extends Model
{
    protected static string $table = 'tags';
    
    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }
    
    public static function allOrdered(): array
    {
        return self::all('name', 'ASC');
    }
}