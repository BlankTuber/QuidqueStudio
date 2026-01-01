<?php

define('BASE_PATH', dirname(__DIR__));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = require BASE_PATH . '/config/config.php';

require BASE_PATH . '/config/autoload.php';

\Quidque\Core\ErrorHandler::init($config['site']['debug'] ?? false);

$db = new \Quidque\Core\Database($config);
\Quidque\Models\Model::setDatabase($db);

\Quidque\Helpers\Mail::init($config);
\Quidque\Helpers\RateLimiter::init(BASE_PATH . '/storage/cache');

\Quidque\Core\Auth::init($config);

$request = new \Quidque\Core\Request();
$router = new \Quidque\Core\Router();

$router->addMiddleware('auth', [\Quidque\Middleware\AuthMiddleware::class, 'requireAuth']);
$router->addMiddleware('admin', [\Quidque\Middleware\AuthMiddleware::class, 'requireAdmin']);
$router->addMiddleware('guest', [\Quidque\Middleware\AuthMiddleware::class, 'requireGuest']);
$router->addMiddleware('csrf', [\Quidque\Middleware\AuthMiddleware::class, 'verifyCsrf']);

require BASE_PATH . '/config/routes.php';

$method = $request->method();
$uri = $request->uri();

echo $router->dispatch($method, $uri);