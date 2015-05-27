<?php

require_once __DIR__.'/../../server/autoload.php';

use \Tudu\Core;
use \Tudu\Conf\Conf;
use \Tudu\Handler;
use \Tudu\Core\Encoder;
use \Tudu\Core\Handler\Auth\Auth as AuthHandler;
use \Tudu\Core\Handler\Auth\Contract\BasicAuthentication;
use \Tudu\Handler\Auth\Contract\TuduAuthentication;
use \Tudu\Handler\Auth\Contract\TuduAuthorization;

$db = new Core\Data\PgSQLConnection([
    'host'     => Conf::DB_HOST,
    'database' => Conf::DB_NAME,
    'username' => Conf::DB_USERNAME,
    'password' => Conf::DB_PASSWORD
]);

$app = new \Tudu\Delegate\Slim(new \Slim\Slim());
$app->setEncoder(new Encoder\JSON());

// User URIs -------------------------------------------------------------------

$app->map('/signin', function () use ($app, $db) {
    (new AuthHandler(
        $app,
        $db,
        new BasicAuthentication()
    ))->process();
}, 'POST');

$app->map('/signin', function () use ($app, $db) {
    (new Handler\Api\User\Signin($app, $db))->process();
});

$app->map('/users/:user_id', function ($user_id) use ($app, $db) {
    (new AuthHandler(
        $app,
        $db,
        new BasicAuthentication(),
        new TuduAuthorization($user_id, $db)
    ))->process();
}, 'PUT');

$app->map('/users/', function () use ($app, $db) {
    (new Handler\Api\User\Users($app, $db))->process();
});

$app->map('/users/:user_id', function ($user_id) use ($app, $db) {
    (new Handler\Api\User\User($app, $db, [
        'user_id' => $user_id
    ]))->process();
});

$app->map('/users/:user_id/confirm', function ($user_id) use ($app, $db) {
    (new Handler\Api\User\Confirm($app, $db, [
        'user_id' => $user_id
    ]))->process();
});

// Task URIs -------------------------------------------------------------------

$app->map('/users/:user_id/tasks/(:task_id)', function ($user_id) use ($app, $db) {
    (new AuthHandler(
        $app,
        $db,
        new TuduAuthentication(),
        new TuduAuthorization($user_id, $db)
    ))->process();
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
