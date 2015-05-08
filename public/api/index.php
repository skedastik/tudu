<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../server/core/delegate/Slim.php';
require_once __DIR__.'/../../server/core/data/PgSQLConnection.php';
require_once __DIR__.'/../../server/conf/conf.php';
require_once __DIR__.'/../../server/handler/api/GetTasksHandler.php';

use \Tudu\Core;
use \Tudu\Conf;
use \Tudu\Handler\Api;

$db = new Core\Data\PgSQLConnection([
    'host'     => Conf\DB_HOST,
    'database' => Conf\DB_NAME,
    'username' => Conf\DB_USERNAME,
    'password' => Conf\DB_PASSWORD
]);

$app = new \Slim\Slim();
$delegate = new Core\Delegate\Slim($app);

$app->get('/users/:user_id/tasks/', function ($user_id) use ($delegate, $db) {
    (new Api\Get\Tasks($delegate, $db, [
        'user_id' => $user_id
    ]))->process();
});

$app->run();
?>
