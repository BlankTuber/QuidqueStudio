<?php

define('BASE_PATH', dirname(__DIR__));

// Load configuration
$config = require BASE_PATH . '/config/config.php';

// Load autoloader
require BASE_PATH . '/config/autoload.php';

// Initialize error handling
\Quidque\Core\ErrorHandler::init($config['site']['debug'] ?? false);

// Initialize database
$db = new \Quidque\Core\Database($config);
\Quidque\Models\Model::setDatabase($db);

// Initialize helpers
\Quidque\Helpers\Mail::init($config);
\Quidque\Helpers\RateLimiter::init(BASE_PATH . '/storage/cache');

// Initialize authentication
\Quidque\Core\Auth::init($config);

// Create request and router instances
$request = new \Quidque\Core\Request();
$router = new \Quidque\Core\Router();

// Register middleware
$router->addMiddleware('auth', [\Quidque\Middleware\AuthMiddleware::class, 'requireAuth']);
$router->addMiddleware('admin', [\Quidque\Middleware\AuthMiddleware::class, 'requireAdmin']);
$router->addMiddleware('guest', [\Quidque\Middleware\AuthMiddleware::class, 'requireGuest']);
$router->addMiddleware('csrf', [\Quidque\Middleware\AuthMiddleware::class, 'verifyCsrf']);

// Load routes
require BASE_PATH . '/config/routes.php';

// Dispatch request
$method = $request->method();
$uri = $request->uri();

echo $router->dispatch($method, $uri);