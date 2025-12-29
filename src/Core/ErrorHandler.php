<?php

namespace Quidque\Core;

class ErrorHandler
{
    private static bool $debug = false;
    
    public static function init(bool $debug = false): void
    {
        self::$debug = $debug;
        
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleError(int $level, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }
        
        throw new \ErrorException($message, 0, $level, $file, $line);
    }
    
    public static function handleException(\Throwable $e): void
    {
        $code = $e->getCode() ?: 500;
        if ($code < 100 || $code > 599) {
            $code = 500;
        }
        
        http_response_code($code);
        
        error_log(sprintf(
            "Exception: %s in %s:%d\nStack trace:\n%s",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));
        
        if (self::$debug) {
            self::renderDebug($e);
        } else {
            self::renderProduction($code);
        }
        
        exit;
    }
    
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }
    
    private static function renderDebug(\Throwable $e): void
    {
        echo '<!DOCTYPE html><html><head><title>Error</title>';
        echo '<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#e5e5e5}';
        echo 'h1{color:#ff00aa}pre{background:#242424;padding:15px;overflow:auto;border-radius:4px}</style></head>';
        echo '<body><h1>Error</h1>';
        echo '<p><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>';
        echo '<p>' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</body></html>';
    }
    
    private static function renderProduction(int $code): void
    {
        $titles = [
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Server Error',
        ];
        
        $title = $titles[$code] ?? 'Error';
        
        $templatePath = BASE_PATH . '/templates/errors/' . $code . '.php';
        if (file_exists($templatePath)) {
            global $config;
            require $templatePath;
        } else {
            echo '<!DOCTYPE html><html><head><title>' . $code . '</title></head>';
            echo '<body><h1>' . $code . ' - ' . $title . '</h1>';
            echo '<p><a href="/">Go home</a></p></body></html>';
        }
    }
}