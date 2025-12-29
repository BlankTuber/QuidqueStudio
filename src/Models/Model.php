<?php

namespace Quidque\Models;

use Quidque\Core\Database;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static Database $db;
    
    public static function setDatabase(Database $db): void
    {
        self::$db = $db;
    }
    
    public static function find(int $id): ?array
    {
        return self::$db->fetch(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
    }
    
    public static function findBy(string $column, mixed $value): ?array
    {
        return self::$db->fetch(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ?",
            [$value]
        );
    }
    
    public static function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        return self::$db->fetchAll(
            "SELECT * FROM " . static::$table . " ORDER BY {$orderBy} {$direction}"
        );
    }
    
    public static function where(string $column, mixed $value, string $orderBy = 'id'): array
    {
        return self::$db->fetchAll(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ? ORDER BY {$orderBy}",
            [$value]
        );
    }
    
    public static function create(array $data): int
    {
        return self::$db->insert(static::$table, $data);
    }
    
    public static function update(int $id, array $data): int
    {
        return self::$db->update(static::$table, $data, static::$primaryKey . " = ?", [$id]);
    }
    
    public static function delete(int $id): int
    {
        return self::$db->delete(static::$table, static::$primaryKey . " = ?", [$id]);
    }
    
    public static function count(string $where = '1=1', array $params = []): int
    {
        $result = self::$db->fetch(
            "SELECT COUNT(*) as count FROM " . static::$table . " WHERE {$where}",
            $params
        );
        return (int) $result['count'];
    }
}