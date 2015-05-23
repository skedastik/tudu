<?php

require_once __DIR__.'/../../server/autoload.php';

use \Tudu\Core;
use \Tudu\Conf\Conf;
use \Tudu\Handler;

$db = new Core\Data\PgSQLConnection([
    'host'     => Conf::DB_HOST,
    'database' => Conf::DB_NAME,
    'username' => Conf::DB_USERNAME,
    'password' => Conf::DB_PASSWORD
]);

$app = new \Tudu\Delegate\Slim(new \Slim\Slim());

// User URIs -------------------------------------------------------------------

$app->map('/users/:user_id', function ($user_id) use ($app, $db) {
    (new Core\Handler\Auth\Basic($app, $db, [
        'user_id' => $user_id
    ]))->process();
}, 'PUT');

$app->map('/users/', function () use ($app, $db) {
    (new Handler\Api\User\Users($app, $db))->process();
});

$app->map('/users/:user_id', function ($user_id) use ($app, $db) {
    (new Handler\Api\User\User($app, $db, [
        'user_id' => $user_id
    ]))->process();
});

// Task URIs -------------------------------------------------------------------

$app->map('/users/:user_id/tasks/(:task_id)', function ($user_id, $task_id = null) use ($app, $db) {
    (new Core\Handler\Auth\HMAC($app, $db, [
        'user_id' => $user_id,
        'task_id' => $task_id
    ]))->process();
});

$app->map('/users/:user_id/tasks/', function ($user_id) use ($app, $db) {
    (new Handler\Api\Task\Tasks($app, $db, [
        'user_id' => $user_id
    ]))->process();
});

$app->map('/users/:user_id/tasks/:task_id', function ($user_id, $task_id) use ($app, $db) {
    (new Handler\Api\Task\Task($app, $db, [
        'user_id' => $user_id,
        'task_id' => $task_id
    ]))->process();
});

$app->run();
?>
