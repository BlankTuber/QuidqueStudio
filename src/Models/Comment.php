<?php

namespace Quidque\Models;

class Comment extends Model
{
    protected static string $table = 'comments';
    
    public static function getForProject(int $projectId): array
    {
        return self::$db->fetchAll(
            "SELECT c.*, u.username, u.settings as user_settings
             FROM " . static::$table . " c
             JOIN users u ON c.user_id = u.id
             WHERE c.project_id = ?
             ORDER BY c.created_at ASC",
            [$projectId]
        );
    }
    
    public static function getThreaded(int $projectId): array
    {
        $all = self::getForProject($projectId);
        
        $threaded = [];
        $children = [];
        
        foreach ($all as $comment) {
            if ($comment['parent_id'] === null) {
                $threaded[$comment['id']] = $comment;
                $threaded[$comment['id']]['replies'] = [];
            } else {
                $children[$comment['parent_id']][] = $comment;
            }
        }
        
        foreach ($children as $parentId => $replies) {
            if (isset($threaded[$parentId])) {
                $threaded[$parentId]['replies'] = $replies;
            }
        }
        
        return array_values($threaded);
    }
    
    public static function createComment(int $projectId, int $userId, string $content, ?int $parentId = null): int
    {
        return self::create([
            'project_id' => $projectId,
            'user_id' => $userId,
            'parent_id' => $parentId,
            'content' => $content,
        ]);
    }
    
    public static function canEdit(int $commentId): bool
    {
        $hasReplies = self::$db->fetch(
            "SELECT COUNT(*) as count FROM " . static::$table . " WHERE parent_id = ?",
            [$commentId]
        );
        return (int) $hasReplies['count'] === 0;
    }
    
    public static function editComment(int $id, string $content): int
    {
        return self::update($id, [
            'content' => $content,
            'is_edited' => 1,
        ]);
    }
    
    public static function softDelete(int $id, string $deletedBy): int
    {
        return self::update($id, [
            'deleted_by' => $deletedBy,
        ]);
    }
    
    public static function countForProject(int $projectId): int
    {
        return self::count('project_id = ? AND deleted_by IS NULL', [$projectId]);
    }
    
    public static function getRecentByUser(int $userId, int $limit = 10): array
    {
        return self::$db->fetchAll(
            "SELECT c.*, p.title as project_title, p.slug as project_slug
             FROM " . static::$table . " c
             JOIN projects p ON c.project_id = p.id
             WHERE c.user_id = ?
             ORDER BY c.created_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }
}