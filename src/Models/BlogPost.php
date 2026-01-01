<?php

namespace Quidque\Models;

use Quidque\Constants;

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
             WHERE bp.status = ?
             ORDER BY bp.published_at DESC
             LIMIT ? OFFSET ?",
            [Constants::POST_PUBLISHED, $limit, $offset]
        );
    }
    
    public static function getDrafts(?int $authorId = null): array
    {
        $sql = "SELECT bp.*, u.username as author_name, bc.name as category_name, bc.slug as category_slug
                FROM " . static::$table . " bp
                JOIN users u ON bp.author_id = u.id
                LEFT JOIN blog_categories bc ON bp.category_id = bc.id
                WHERE bp.status = ?";
        $params = [Constants::POST_DRAFT];
        
        if ($authorId !== null) {
            $sql .= " AND bp.author_id = ?";
            $params[] = $authorId;
        }
        
        $sql .= " ORDER BY bp.updated_at DESC";
        
        return self::$db->fetchAll($sql, $params);
    }
    
    public static function getByCategory(string $categorySlug, int $limit = 10): array
    {
        return self::$db->fetchAll(
            "SELECT bp.*, u.username as author_name, bc.name as category_name, bc.slug as category_slug
             FROM " . static::$table . " bp
             JOIN users u ON bp.author_id = u.id
             JOIN blog_categories bc ON bp.category_id = bc.id
             WHERE bp.status = ? AND bc.slug = ?
             ORDER BY bp.published_at DESC
             LIMIT ?",
            [Constants::POST_PUBLISHED, $categorySlug, $limit]
        );
    }
    
    public static function getByTag(string $tagSlug, int $limit = 10): array
    {
        return self::$db->fetchAll(
            "SELECT bp.*, u.username as author_name, bc.name as category_name, bc.slug as category_slug
             FROM " . static::$table . " bp
             JOIN users u ON bp.author_id = u.id
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id
             JOIN blog_post_tags bpt ON bp.id = bpt.post_id
             JOIN blog_tags bt ON bpt.tag_id = bt.id
             WHERE bp.status = ? AND bt.slug = ?
             ORDER BY bp.published_at DESC
             LIMIT ?",
            [Constants::POST_PUBLISHED, $tagSlug, $limit]
        );
    }
    
    public static function getRecent(int $limit = 4): array
    {
        return self::getPublished($limit);
    }
    
    public static function publish(int $id): int
    {
        return self::update($id, [
            'status' => Constants::POST_PUBLISHED,
            'published_at' => date('Y-m-d H:i:s'),
        ]);
    }
    
    public static function unpublish(int $id): int
    {
        return self::$db->query(
            "UPDATE " . static::$table . " SET status = ?, published_at = NULL WHERE id = ?",
            [Constants::POST_DRAFT, $id]
        )->rowCount();
    }
    
    public static function isPublished(int $id): bool
    {
        $post = self::find($id);
        return $post !== null && $post['status'] === Constants::POST_PUBLISHED;
    }
    
    public static function getTags(int $postId): array
    {
        return self::$db->fetchAll(
            "SELECT bt.* FROM blog_tags bt
             JOIN blog_post_tags bpt ON bt.id = bpt.tag_id
             WHERE bpt.post_id = ?
             ORDER BY bt.name",
            [$postId]
        );
    }
    
    public static function setTags(int $postId, array $tagIds): void
    {
        self::$db->delete('blog_post_tags', 'post_id = ?', [$postId]);
        
        foreach ($tagIds as $tagId) {
            self::$db->insert('blog_post_tags', [
                'post_id' => $postId,
                'tag_id' => (int) $tagId,
            ]);
        }
    }
    
    public static function countPublished(): int
    {
        return self::count("status = ?", [Constants::POST_PUBLISHED]);
    }
    
    public static function countDrafts(): int
    {
        return self::count("status = ?", [Constants::POST_DRAFT]);
    }
    
    public static function search(string $query, int $limit = 20): array
    {
        $searchTerm = '%' . $query . '%';
        return self::$db->fetchAll(
            "SELECT bp.*, u.username as author_name, bc.name as category_name, bc.slug as category_slug
             FROM " . static::$table . " bp
             JOIN users u ON bp.author_id = u.id
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id
             WHERE bp.status = ? AND (bp.title LIKE ? OR bp.slug LIKE ?)
             ORDER BY bp.published_at DESC
             LIMIT ?",
            [Constants::POST_PUBLISHED, $searchTerm, $searchTerm, $limit]
        );
    }
    
    public static function getExcerpt(int $postId, int $maxLength = 200): string
    {
        $blocks = BlogBlock::getForPost($postId);
        
        foreach ($blocks as $block) {
            if ($block['block_type_slug'] === 'text') {
                $data = json_decode($block['data'], true) ?? [];
                if (!empty($data['content'])) {
                    $text = strip_tags($data['content']);
                    if (mb_strlen($text) > $maxLength) {
                        return mb_substr($text, 0, $maxLength - 3) . '...';
                    }
                    return $text;
                }
            }
        }
        
        return '';
    }
}