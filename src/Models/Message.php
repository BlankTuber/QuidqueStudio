<?php

namespace Quidque\Models;

class Message extends Model
{
    protected static string $table = 'messages';
    
    public static function getConversation(int $userId): array
    {
        return self::$db->fetchAll(
            "SELECT * FROM " . static::$table . "
             WHERE user_id = ?
             ORDER BY created_at ASC",
            [$userId]
        );
    }
    
    public static function send(int $userId, string $content, bool $isFromAdmin = false, ?string $imagePath = null): int
    {
        return self::create([
            'user_id' => $userId,
            'is_from_admin' => $isFromAdmin ? 1 : 0,
            'content' => $content,
            'image_path' => $imagePath,
        ]);
    }
    
    public static function sendFromUser(int $userId, string $content, ?string $imagePath = null): int
    {
        return self::send($userId, $content, false, $imagePath);
    }
    
    public static function sendFromAdmin(int $userId, string $content, ?string $imagePath = null): int
    {
        return self::send($userId, $content, true, $imagePath);
    }
    
    public static function getAllConversations(): array
    {
        return self::$db->fetchAll(
            "SELECT m.*, u.username, u.email,
                    (SELECT COUNT(*) FROM " . static::$table . " m2 
                     WHERE m2.user_id = m.user_id AND m2.is_from_admin = 0 
                     AND m2.created_at > COALESCE(
                         (SELECT MAX(m3.created_at) FROM " . static::$table . " m3 
                          WHERE m3.user_id = m.user_id AND m3.is_from_admin = 1), 
                         '1970-01-01'
                     )) as unread_count
             FROM " . static::$table . " m
             JOIN users u ON m.user_id = u.id
             WHERE m.id IN (
                 SELECT MAX(id) FROM " . static::$table . " GROUP BY user_id
             )
             ORDER BY m.created_at DESC"
        );
    }
    
    public static function cleanup(int $daysOld = 30): int
    {
        return self::$db->query(
            "DELETE FROM " . static::$table . " WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$daysOld]
        )->rowCount();
    }
    
    public static function hasUnread(int $userId): bool
    {
        $result = self::$db->fetch(
            "SELECT COUNT(*) as count FROM " . static::$table . "
             WHERE user_id = ? AND is_from_admin = 1
             AND created_at > COALESCE(
                 (SELECT MAX(created_at) FROM " . static::$table . " 
                  WHERE user_id = ? AND is_from_admin = 0),
                 '1970-01-01'
             )",
            [$userId, $userId]
        );
        return (int) $result['count'] > 0;
    }
}