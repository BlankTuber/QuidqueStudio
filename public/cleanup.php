<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/config/autoload.php';

$config = require BASE_PATH . '/config/config.php';

use Quidque\Core\Database;
use Quidque\Models\Model;
use Quidque\Helpers\Cleanup;

$db = new Database($config);
Model::setDatabase($db);

$results = Cleanup::run();
Cleanup::log($results);

if (php_sapi_name() === 'cli') {
    echo "Cleaned: {$results['tokens']} tokens, {$results['sessions']} sessions, {$results['messages']} messages\n";
} else {
    header('Content-Type: application/json');
    echo json_encode($results);
}