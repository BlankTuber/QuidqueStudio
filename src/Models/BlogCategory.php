<?php

namespace Quidque\Models;

class BlogCategory extends Model
{
    protected static string $table = 'blog_categories';
    
    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }
    
    public static function allOrdered(): array
    {
        return self::all('name', 'ASC');
    }
    
    public static function getPostCount(int $categoryId): int
    {
        $result = self::$db->fetch(
            "SELECT COUNT(*) as count FROM blog_posts WHERE category_id = ? AND status = 'published'",
            [$categoryId]
        );
        return (int) $result['count'];
    }
}