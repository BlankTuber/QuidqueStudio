<?php

/**
 * Custom PSR-4 Autoloader
 * Maps Quidque\Core\Database -> src/Core/Database.php
 */

spl_autoload_register(function (string $class): void {
    $prefix = 'Quidque\\';
    $baseDir = dirname(__DIR__) . '/src/';
    
    $prefixLength = strlen($prefix);
    if (strncmp($prefix, $class, $prefixLength) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $prefixLength);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});