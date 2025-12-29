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
            'messages' => Message::cleanup(30),
        ];
        
        return $results;
    }
    
    public static function log(array $results): void
    {
        $logFile = BASE_PATH . '/storage/logs/cleanup.log';
        
        $entry = sprintf(
            "[%s] Cleaned: %d tokens, %d sessions, %d messages\n",
            date('Y-m-d H:i:s'),
            $results['tokens'],
            $results['sessions'],
            $results['messages']
        );
        
        file_put_contents($logFile, $entry, FILE_APPEND);
    }
}