<?php

namespace Quidque\Models;

class AuthToken extends Model
{
    protected static string $table = 'auth_tokens';
    
    public static function generate(int $userId, int $expirySeconds): string
    {
        $token = bin2hex(random_bytes(32));
        
        self::$db->query(
            "INSERT INTO " . static::$table . " (user_id, token, expires_at, used) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), 0)",
            [$userId, $token, $expirySeconds]
        );
        
        return $token;
    }
    
    public static function findValid(string $token): ?array
    {
        return self::$db->fetch(
            "SELECT * FROM " . static::$table . " 
             WHERE token = ? AND used = 0 AND expires_at > NOW()",
            [$token]
        );
    }
    
    public static function markUsed(int $id): int
    {
        return self::update($id, ['used' => 1]);
    }
    
    public static function canRequestNew(int $userId, int $cooldownSeconds): bool
    {
        $recent = self::$db->fetch(
            "SELECT * FROM " . static::$table . " 
             WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
             ORDER BY created_at DESC LIMIT 1",
            [$userId, $cooldownSeconds]
        );
        return $recent === null;
    }
    
    public static function cleanup(): int
    {
        return self::$db->query(
            "DELETE FROM " . static::$table . " WHERE expires_at < NOW() OR used = 1"
        )->rowCount();
    }
}