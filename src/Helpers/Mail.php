<?php

namespace Quidque\Helpers;

class Mail
{
    private static array $config;
    
    public static function init(array $config): void
    {
        self::$config = $config;
    }
    
    public static function send(string $to, string $subject, string $body): bool
    {
        $debug = self::$config['site']['debug'] ?? false;
        
        if ($debug) {
            return self::log($to, $subject, $body);
        }
        
        $headers = [
            'From: noreply@' . parse_url(self::$config['site']['url'], PHP_URL_HOST),
            'Content-Type: text/html; charset=UTF-8',
        ];
        
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
    
    private static function log(string $to, string $subject, string $body): bool
    {
        $logFile = BASE_PATH . '/storage/logs/mail.log';
        
        $entry = sprintf(
            "[%s]\nTO: %s\nSUBJECT: %s\nBODY:\n%s\n%s\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $body,
            str_repeat('-', 50)
        );
        
        return file_put_contents($logFile, $entry, FILE_APPEND) !== false;
    }
    
    public static function sendLoginToken(string $email, string $token): bool
    {
        $url = self::$config['site']['url'] . '/auth/verify?token=' . $token;
        
        $subject = 'Your login link - ' . self::$config['site']['name'];
        
        $body = "
            <h2>Login to " . self::$config['site']['name'] . "</h2>
            <p>Click the link below to log in. This link expires in 15 minutes.</p>
            <p><a href=\"{$url}\">{$url}</a></p>
            <p>If you didn't request this, you can ignore this email.</p>
        ";
        
        return self::send($email, $subject, $body);
    }
    
    public static function sendNewLoginAlert(string $email, array $deviceInfo): bool
    {
        $subject = 'New login detected - ' . self::$config['site']['name'];
        
        $location = array_filter([$deviceInfo['city'] ?? null, $deviceInfo['country'] ?? null]);
        $locationStr = $location ? implode(', ', $location) : 'Unknown location';
        
        $body = "
            <h2>New login to your account</h2>
            <p>A new login was detected:</p>
            <ul>
                <li><strong>Location:</strong> {$locationStr}</li>
                <li><strong>IP:</strong> {$deviceInfo['ip']}</li>
                <li><strong>Device:</strong> {$deviceInfo['user_agent']}</li>
                <li><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</li>
            </ul>
            <p>If this wasn't you, please contact us immediately.</p>
        ";
        
        return self::send($email, $subject, $body);
    }
}