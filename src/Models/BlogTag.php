<?php

namespace Quidque\Models;

use Quidque\Helpers\Str;

class BlogTag extends Model
{
    protected static string $table = 'blog_tags';
    
    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }
    
    public static function allOrdered(): array
    {
        return self::all('name', 'ASC');
    }
    
    public static function findOrCreate(string $name): int
    {
        $slug = Str::slug($name);
        $existing = self::findBySlug($slug);
        
        if ($existing) {
            return $existing['id'];
        }
        
        return self::create([
            'name' => $name,
            'slug' => $slug,
        ]);
    }
    
    public static function getPostCount(int $tagId): int
    {
        $result = self::$db->fetch(
            "SELECT COUNT(*) as count 
             FROM blog_post_tags bpt
             JOIN blog_posts bp ON bpt.post_id = bp.id
             WHERE bpt.tag_id = ? AND bp.status = 'published'",
            [$tagId]
        );
        return (int) $result['count'];
    }
}