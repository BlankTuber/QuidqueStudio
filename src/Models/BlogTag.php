<?php

namespace Quidque\Models;

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
        $slug = self::slugify($name);
        $existing = self::findBySlug($slug);
        
        if ($existing) {
            return $existing['id'];
        }
        
        return self::create([
            'name' => $name,
            'slug' => $slug,
        ]);
    }
    
    private static function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}