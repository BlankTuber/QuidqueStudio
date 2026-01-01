<?php

namespace Quidque\Models;

use Quidque\Constants;

class Project extends Model
{
    protected static string $table = 'projects';
    
    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }
    
    public static function getFeatured(int $limit = 3): array
    {
        return self::$db->fetchAll(
            "SELECT * FROM " . static::$table . " 
             WHERE is_featured = 1 AND status = ? 
             ORDER BY updated_at DESC LIMIT ?",
            [Constants::PROJECT_ACTIVE, $limit]
        );
    }
    
    public static function getByStatus(string $status): array
    {
        if (!in_array($status, Constants::PROJECT_STATUSES)) {
            return [];
        }
        return self::where('status', $status, 'updated_at DESC');
    }
    
    public static function getActive(): array
    {
        return self::getByStatus(Constants::PROJECT_ACTIVE);
    }
    
    public static function getAllWithTags(?string $status = null, ?string $tagSlug = null): array
    {
        $sql = "SELECT p.*, GROUP_CONCAT(DISTINCT t.name) as tag_names, GROUP_CONCAT(DISTINCT t.slug) as tag_slugs
                FROM " . static::$table . " p
                LEFT JOIN project_tags pt ON p.id = pt.project_id
                LEFT JOIN tags t ON pt.tag_id = t.id";
        
        $where = [];
        $params = [];
        
        if ($status !== null) {
            if (!in_array($status, Constants::PROJECT_STATUSES)) {
                return [];
            }
            $where[] = "p.status = ?";
            $params[] = $status;
        }
        
        if ($tagSlug !== null) {
            $sql .= " INNER JOIN project_tags pt2 ON p.id = pt2.project_id
                      INNER JOIN tags t2 ON pt2.tag_id = t2.id AND t2.slug = ?";
            $params[] = $tagSlug;
        }
        
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " GROUP BY p.id ORDER BY p.updated_at DESC";
        
        return self::$db->fetchAll($sql, $params);
    }
    
    public static function getPaginated(int $page, int $perPage = 12, ?string $status = null): array
    {
        $where = '1=1';
        $params = [];
        
        if ($status !== null && in_array($status, Constants::PROJECT_STATUSES)) {
            $where = 'status = ?';
            $params[] = $status;
        }
        
        return self::paginate($page, $perPage, 'updated_at', 'DESC', $where, $params);
    }
    
    public static function getTags(int $projectId): array
    {
        return self::$db->fetchAll(
            "SELECT t.* FROM tags t
             JOIN project_tags pt ON t.id = pt.tag_id
             WHERE pt.project_id = ?
             ORDER BY t.name",
            [$projectId]
        );
    }
    
    public static function setTags(int $projectId, array $tagIds): void
    {
        self::$db->delete('project_tags', 'project_id = ?', [$projectId]);
        
        $tagIds = array_slice(array_unique(array_map('intval', $tagIds)), 0, Constants::MAX_PROJECT_TAGS);
        
        foreach ($tagIds as $tagId) {
            self::$db->insert('project_tags', [
                'project_id' => $projectId,
                'tag_id' => $tagId,
            ]);
        }
    }
    
    public static function getTechStack(int $projectId): array
    {
        return self::$db->fetchAll(
            "SELECT ts.* FROM tech_stack ts
             JOIN project_tech_stack pts ON ts.id = pts.tech_id
             WHERE pts.project_id = ?
             ORDER BY ts.tier, ts.name",
            [$projectId]
        );
    }
    
    public static function setTechStack(int $projectId, array $techIds): void
    {
        self::$db->delete('project_tech_stack', 'project_id = ?', [$projectId]);
        
        foreach (array_unique(array_map('intval', $techIds)) as $techId) {
            self::$db->insert('project_tech_stack', [
                'project_id' => $projectId,
                'tech_id' => $techId,
            ]);
        }
    }
    
    public static function getSettings(int $projectId): array
    {
        $project = self::find($projectId);
        if (!$project || !$project['settings']) {
            return ['devlog_enabled' => false, 'comments_enabled' => false];
        }
        $settings = json_decode($project['settings'], true);
        return $settings ?? ['devlog_enabled' => false, 'comments_enabled' => false];
    }
    
    public static function updateSettings(int $projectId, array $settings): int
    {
        return self::update($projectId, ['settings' => json_encode($settings)]);
    }
    
    public static function search(string $query, int $limit = 20): array
    {
        $searchTerm = '%' . $query . '%';
        return self::$db->fetchAll(
            "SELECT p.*, GROUP_CONCAT(DISTINCT t.name) as tag_names, GROUP_CONCAT(DISTINCT t.slug) as tag_slugs
             FROM " . static::$table . " p
             LEFT JOIN project_tags pt ON p.id = pt.project_id
             LEFT JOIN tags t ON pt.tag_id = t.id
             WHERE p.title LIKE ? OR p.description LIKE ? OR p.slug LIKE ?
             GROUP BY p.id
             ORDER BY p.updated_at DESC
             LIMIT ?",
            [$searchTerm, $searchTerm, $searchTerm, $limit]
        );
    }
    
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, Constants::PROJECT_STATUSES);
    }
    
    public static function getCurrentlyWorkingOn(): ?array
    {
        $projects = self::$db->fetchAll(
            "SELECT * FROM " . static::$table . " 
             WHERE status = ? 
             ORDER BY updated_at DESC LIMIT 1",
            [Constants::PROJECT_ACTIVE]
        );
        return $projects[0] ?? null;
    }
}