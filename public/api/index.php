<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../server/core/delegate/Slim.php';
require_once __DIR__.'/../../server/core/data/PgSQLConnection.php';
require_once __DIR__.'/../../server/conf/conf.php';
require_once __DIR__.'/../../server/core/HMACHandler.php';
require_once __DIR__.'/../../server/handler/api/TasksHandler.php';

use \Tudu\Core;
use \Tudu\Conf;
use \Tudu\Handler;

$db = new Core\Data\PgSQLConnection([
    'host'     => Conf\DB_HOST,
    'database' => Conf\DB_NAME,
    'username' => Conf\DB_USERNAME,
    'password' => Conf\DB_PASSWORD
]);

$app = new \Slim\Slim();
$delegate = new Core\Delegate\Slim($app);

$delegate->map('/users/:user_id/tasks/(:task_id)', function ($user_id, $task_id = null) use ($delegate, $db) {
    (new Core\HMACHandler($delegate, $db, [
        'user_id' => $user_id,
        'task_id' => $task_id
    ]))->process();
});

$delegate->map('/users/:user_id/tasks/(:task_id)', function ($user_id, $task_id = null) use ($delegate, $db) {
    (new Handler\Api\Tasks($delegate, $db, [
        'user_id' => $user_id,
        'task_id' => $task_id
    ]))->process();
});

$app->run();
?>
