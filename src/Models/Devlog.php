<?php

namespace Quidque\Models;

class Devlog extends Model
{
    protected static string $table = 'devlogs';
    
    public static function findBySlug(int $projectId, string $slug): ?array
    {
        return self::$db->fetch(
            "SELECT * FROM " . static::$table . " WHERE project_id = ? AND slug = ?",
            [$projectId, $slug]
        );
    }
    
    public static function getForProject(int $projectId, int $limit = 10): array
    {
        return self::$db->fetchAll(
            "SELECT * FROM " . static::$table . "
             WHERE project_id = ?
             ORDER BY created_at DESC
             LIMIT ?",
            [$projectId, $limit]
        );
    }
    
    public static function getRecent(int $limit = 5): array
    {
        return self::$db->fetchAll(
            "SELECT d.*, p.title as project_title, p.slug as project_slug
             FROM " . static::$table . " d
             JOIN projects p ON d.project_id = p.id
             WHERE p.status = 'active'
             ORDER BY d.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
    
    public static function countForProject(int $projectId): int
    {
        return self::count('project_id = ?', [$projectId]);
    }
    
    public static function createEntry(int $projectId, string $title, string $content): int
    {
        $slug = self::generateSlug($projectId, $title);
        
        return self::create([
            'project_id' => $projectId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
        ]);
    }
    
    private static function generateSlug(int $projectId, string $title): string
    {
        $base = strtolower(trim($title));
        $base = preg_replace('/[^a-z0-9-]/', '-', $base);
        $base = preg_replace('/-+/', '-', $base);
        $base = trim($base, '-');
        
        $slug = $base;
        $counter = 1;
        
        while (self::findBySlug($projectId, $slug)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}