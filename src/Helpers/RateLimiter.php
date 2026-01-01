<?php

namespace Quidque\Helpers;

class RateLimiter
{
    private static string $cacheDir;
    
    public static function init(string $cacheDir): void
    {
        self::$cacheDir = rtrim($cacheDir, '/') . '/rate_limits/';
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0775, true);
        }
    }
    
    public static function attempt(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $file = self::getFile($key);
        $data = self::getData($file);
        
        $now = time();
        $data['attempts'] = array_filter(
            $data['attempts'] ?? [],
            fn($timestamp) => $timestamp > ($now - $decaySeconds)
        );
        
        if (count($data['attempts']) >= $maxAttempts) {
            return false;
        }
        
        $data['attempts'][] = $now;
        self::setData($file, $data);
        
        return true;
    }
    
    public static function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $file = self::getFile($key);
        $data = self::getData($file);
        
        $now = time();
        $recentAttempts = array_filter(
            $data['attempts'] ?? [],
            fn($timestamp) => $timestamp > ($now - $decaySeconds)
        );
        
        return count($recentAttempts) >= $maxAttempts;
    }
    
    public static function hit(string $key): void
    {
        $file = self::getFile($key);
        $data = self::getData($file);
        $data['attempts'][] = time();
        self::setData($file, $data);
    }
    
    public static function clear(string $key): void
    {
        $file = self::getFile($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public static function remainingAttempts(string $key, int $maxAttempts, int $decaySeconds): int
    {
        $file = self::getFile($key);
        $data = self::getData($file);
        
        $now = time();
        $recentAttempts = array_filter(
            $data['attempts'] ?? [],
            fn($timestamp) => $timestamp > ($now - $decaySeconds)
        );
        
        return max(0, $maxAttempts - count($recentAttempts));
    }
    
    public static function availableIn(string $key, int $decaySeconds): int
    {
        $file = self::getFile($key);
        $data = self::getData($file);
        
        if (empty($data['attempts'])) {
            return 0;
        }
        
        $oldestAttempt = min($data['attempts']);
        $availableAt = $oldestAttempt + $decaySeconds;
        
        return max(0, $availableAt - time());
    }
    
    public static function cleanup(int $maxAge = 86400): int
    {
        $count = 0;
        $files = glob(self::$cacheDir . '*.json');
        $cutoff = time() - $maxAge;
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    private static function getFile(string $key): string
    {
        return self::$cacheDir . md5($key) . '.json';
    }
    
    private static function getData(string $file): array
    {
        if (!file_exists($file)) {
            return ['attempts' => []];
        }
        
        $content = file_get_contents($file);
        return json_decode($content, true) ?? ['attempts' => []];
    }
    
    private static function setData(string $file, array $data): void
    {
        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}