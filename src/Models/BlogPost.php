<?php

namespace Quidque\Models;

class BlogPost extends Model
{
    protected static string $table = 'blog_posts';
    
    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }
    
    public static function getPublished(int $limit = 10, int $offset = 0): array
    {
        return self::$db->fetchAll(
            "SELECT bp.*, u.username as author_name, bc.name as category_name, bc.slug as category_slug
             FROM " . static::$table . " bp
             JOIN users u ON bp.author_id = u.id
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id
             WHERE bp.status = 'published'
             ORDER BY bp.published_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }
    
    public static function getDrafts(int $authorId = null): array
    {
        $sql = "SELECT bp.*, u.username as author_name
                FROM " . static::$table . " bp
                JOIN users u ON bp.author_id = u.id
                WHERE bp.status = 'draft'";
        $params = [];
        
        if ($authorId) {
            $sql .= " AND bp.author_id = ?";
            $params[] = $authorId;
        }
        
        $sql .= " ORDER BY bp.updated_at DESC";
        
        return self::$db->fetchAll($sql, $params);
    }
    
    public static function getByCategory(string $categorySlug, int $limit = 10): array
    {
        return self::$db->fetchAll(
            "SELECT bp.*, u.username as author_name
             FROM " . static::$table . " bp
             JOIN users u ON bp.author_id = u.id
             JOIN blog_categories bc ON bp.category_id = bc.id
             WHERE bp.status = 'published' AND bc.slug = ?
             ORDER BY bp.published_at DESC
             LIMIT ?",
            [$categorySlug, $limit]
        );
    }
    
    public static function getByTag(string $tagSlug, int $limit = 10): array
    {
        return self::$db->fetchAll(
            "SELECT bp.*, u.username as author_name
             FROM " . static::$table . " bp
             JOIN users u ON bp.author_id = u.id
             JOIN blog_post_tags bpt ON bp.id = bpt.post_id
             JOIN blog_tags bt ON bpt.tag_id = bt.id
             WHERE bp.status = 'published' AND bt.slug = ?
             ORDER BY bp.published_at DESC
             LIMIT ?",
            [$tagSlug, $limit]
        );
    }
    
    public static function getRecent(int $limit = 4): array
    {
        return self::getPublished($limit);
    }
    
    public static function publish(int $id): int
    {
        return self::update($id, [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s'),
        ]);
    }
    
    public static function unpublish(int $id): int
    {
        return self::update($id, [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
    
    public static function getTags(int $postId): array
    {
        return self::$db->fetchAll(
            "SELECT bt.* FROM blog_tags bt
             JOIN blog_post_tags bpt ON bt.id = bpt.tag_id
             WHERE bpt.post_id = ?",
            [$postId]
        );
    }
    
    public static function setTags(int $postId, array $tagIds): void
    {
        self::$db->delete('blog_post_tags', 'post_id = ?', [$postId]);
        
        foreach ($tagIds as $tagId) {
            self::$db->insert('blog_post_tags', [
                'post_id' => $postId,
                'tag_id' => $tagId,
            ]);
        }
    }
    
    public static function countPublished(): int
    {
        return self::count("status = 'published'");
    }
}