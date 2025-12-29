<?php

namespace Quidque\Models;

class User extends Model
{
    protected static string $table = 'users';
    
    public static function findByEmail(string $email): ?array
    {
        return self::findBy('email', $email);
    }
    
    public static function findByUsername(string $username): ?array
    {
        return self::findBy('username', $username);
    }
    
    public static function emailExists(string $email): bool
    {
        return self::findByEmail($email) !== null;
    }
    
    public static function usernameExists(string $username): bool
    {
        return self::findByUsername($username) !== null;
    }
    
    public static function register(string $email, string $username): int
    {
        return self::create([
            'email' => $email,
            'username' => $username,
            'is_admin' => 0,
            'is_confirmed' => 0,
        ]);
    }
    
    public static function confirm(int $id): int
    {
        return self::update($id, ['is_confirmed' => 1]);
    }
    
    public static function getSettings(int $id): array
    {
        $user = self::find($id);
        if (!$user) {
            return [];
        }
        return json_decode($user['settings'], true) ?? [];
    }
    
    public static function updateSettings(int $id, array $settings): int
    {
        return self::update($id, ['settings' => json_encode($settings)]);
    }
}