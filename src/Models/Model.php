<?php

namespace Quidque\Models;

use Quidque\Core\Database;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static string $primaryKeyType = 'int'; // 'int' or 'string'
    protected static Database $db;
    
    public static function setDatabase(Database $db): void
    {
        self::$db = $db;
    }
    
    public static function find(int|string $id): ?array
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
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
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
    
    public static function whereIn(string $column, array $values, string $orderBy = 'id'): array
    {
        if (empty($values)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        return self::$db->fetchAll(
            "SELECT * FROM " . static::$table . " WHERE {$column} IN ({$placeholders}) ORDER BY {$orderBy}",
            $values
        );
    }
    
    public static function create(array $data): int|string
    {
        $id = self::$db->insert(static::$table, $data);
        
        if (static::$primaryKeyType === 'string' && isset($data[static::$primaryKey])) {
            return $data[static::$primaryKey];
        }
        
        return $id;
    }
    
    public static function update(int|string $id, array $data): int
    {
        return self::$db->update(static::$table, $data, static::$primaryKey . " = ?", [$id]);
    }
    
    public static function delete(int|string $id): int
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
    
    public static function exists(int|string $id): bool
    {
        $result = self::$db->fetch(
            "SELECT 1 FROM " . static::$table . " WHERE " . static::$primaryKey . " = ? LIMIT 1",
            [$id]
        );
        return $result !== null;
    }
    
    public static function paginate(int $page, int $perPage, string $orderBy = 'id', string $direction = 'DESC', string $where = '1=1', array $params = []): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;
        
        $total = self::count($where, $params);
        $totalPages = (int) ceil($total / $perPage);
        
        $items = self::$db->fetchAll(
            "SELECT * FROM " . static::$table . " WHERE {$where} ORDER BY {$orderBy} {$direction} LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'has_more' => $page < $totalPages,
        ];
    }
}