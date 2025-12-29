<?php

namespace Quidque\Models;

class TechStack extends Model
{
    protected static string $table = 'tech_stack';
    
    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }
    
    public static function getByTier(int $tier): array
    {
        return self::where('tier', $tier, 'name ASC');
    }
    
    public static function allGroupedByTier(): array
    {
        $all = self::$db->fetchAll(
            "SELECT * FROM " . static::$table . " ORDER BY tier, name"
        );
        
        $grouped = [];
        foreach ($all as $tech) {
            $grouped[$tech['tier']][] = $tech;
        }
        return $grouped;
    }
}