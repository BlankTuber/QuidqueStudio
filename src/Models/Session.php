<?php

namespace Quidque\Models;

use Quidque\Helpers\Str;

class Session extends Model
{
    protected static string $table = 'sessions';
    protected static string $primaryKey = 'id';
    protected static string $primaryKeyType = 'string';
    
    public static function generate(int $userId, int $lifetimeSeconds, array $deviceInfo = []): string
    {
        $id = Str::random(64);
        
        self::$db->insert(static::$table, [
            'id' => $id,
            'user_id' => $userId,
            'ip_address' => $deviceInfo['ip'] ?? null,
            'user_agent' => $deviceInfo['user_agent'] ?? null,
            'country' => $deviceInfo['country'] ?? null,
            'city' => $deviceInfo['city'] ?? null,
            'expires_at' => date('Y-m-d H:i:s', time() + $lifetimeSeconds),
        ]);
        
        return $id;
    }
    
    public static function findValid(string $id): ?array
    {
        return self::$db->fetch(
            "SELECT s.*, u.* FROM " . static::$table . " s
             JOIN users u ON s.user_id = u.id
             WHERE s.id = ? AND s.expires_at > NOW()",
            [$id]
        );
    }
    
    public static function refresh(string $id, int $lifetimeSeconds): int
    {
        return self::$db->update(
            static::$table,
            [
                'expires_at' => date('Y-m-d H:i:s', time() + $lifetimeSeconds),
                'last_active_at' => date('Y-m-d H:i:s'),
            ],
            "id = ?",
            [$id]
        );
    }
    
    public static function destroy(string $id): int
    {
        return self::$db->delete(static::$table, "id = ?", [$id]);
    }
    
    public static function destroyAllForUser(int $userId): int
    {
        return self::$db->delete(static::$table, "user_id = ?", [$userId]);
    }
    
    public static function getUserSessions(int $userId): array
    {
        return self::$db->fetchAll(
            "SELECT * FROM " . static::$table . " 
             WHERE user_id = ? AND expires_at > NOW() 
             ORDER BY last_active_at DESC",
            [$userId]
        );
    }
    
    public static function countForUser(int $userId): int
    {
        return self::count('user_id = ? AND expires_at > NOW()', [$userId]);
    }
    
    public static function cleanup(): int
    {
        return self::$db->query(
            "DELETE FROM " . static::$table . " WHERE expires_at < NOW()"
        )->rowCount();
    }
}