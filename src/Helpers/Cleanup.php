<?php

namespace Quidque\Helpers;

use Quidque\Models\AuthToken;
use Quidque\Models\Session;
use Quidque\Models\Message;

class Cleanup
{
    public static function run(): array
    {
        $results = [
            'tokens' => AuthToken::cleanup(),
            'sessions' => Session::cleanup(),
            'messages' => 0, // No longer auto-deleting messages
            'rate_limits' => RateLimiter::cleanup(86400),
        ];
        
        return $results;
    }
    
    public static function runWithMessages(int $messageDaysOld = 90): array
    {
        $results = self::run();
        $results['messages'] = Message::cleanup($messageDaysOld);
        return $results;
    }
    
    public static function log(array $results): void
    {
        $logFile = BASE_PATH . '/storage/logs/cleanup.log';
        
        $parts = [];
        foreach ($results as $key => $count) {
            $parts[] = "{$count} {$key}";
        }
        
        $entry = sprintf(
            "[%s] Cleaned: %s\n",
            date('Y-m-d H:i:s'),
            implode(', ', $parts)
        );
        
        file_put_contents($logFile, $entry, FILE_APPEND);
    }
}