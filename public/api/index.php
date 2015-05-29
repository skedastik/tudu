<?php

require_once __DIR__.'/../../server/autoload.php';

use \Tudu\Conf\Conf;
use \Tudu\Handler;
use \Tudu\Core\Handler\Auth\Auth as AuthHandler;
use \Tudu\Handler\Auth\Contract\BasicAuthentication;
use \Tudu\Handler\Auth\Contract\TuduAuthentication;
use \Tudu\Handler\Auth\Contract\TuduAuthorization;
use \Tudu\Data\Model\User;

$db = new \Tudu\Core\Data\PgSQLConnection([
    'host'     => Conf::DB_HOST,
    'database' => Conf::DB_NAME,
    'username' => Conf::DB_USERNAME,
    'password' => Conf::DB_PASSWORD
]);

$app = new \Tudu\Delegate\Slim(new \Slim\Slim());
$app->addEncoder(new \Tudu\Core\Encoder\JSON());

$passwordDelegate = new \Tudu\Delegate\PHPass();
$basicAuthentication = new BasicAuthentication($db, $passwordDelegate);

// User URIs -------------------------------------------------------------------

$app->map('/users/', function () use ($app, $db, $passwordDelegate) {
    (new Handler\Api\User\Users(
        $app,
        $db,
        $passwordDelegate
    ))->process();
});

$app->post('/signin', function () use ($app, $db, $basicAuthentication) {
    (new AuthHandler(
        $app,
        $db,
        $basicAuthentication,
        new TuduAuthorization($db)
    ))->process();
});

$app->map('/signin', function () use ($app, $db) {
    (new Handler\Api\User\Signin(
        $app,
        $db
    ))->process();
});

$app->put('/users/:user_id', function ($userId) use ($app, $db, $basicAuthentication) {
    (new AuthHandler(
        $app,
        $db,
        $basicAuthentication,
        new TuduAuthorization($db, $userId)
    ))->process();
});

$app->map('/users/:user_id', function ($userId) use ($app, $db) {
    $app->setContext([
        User::USER_ID => $userId
    ]);
    (new Handler\Api\User\User($app, $db))->process();
});

$app->map('/users/:user_id/confirm', function ($userId) use ($app, $db) {
    $app->setContext([
        User::USER_ID => $userId
    ]);
    (new Handler\Api\User\Confirm($app, $db))->process();
});

// Task URIs -------------------------------------------------------------------

$app->map('/users/:user_id/tasks/(:task_id)', function ($userId) use ($app, $db) {
    (new AuthHandler(
        $app,
        $db,
        new TuduAuthentication($app, $db, $userId),
        new TuduAuthorization($db, $userId)
    ))->process();
}, 'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD');

$app->map('/users/:user_id/tasks/', function ($userId) use ($app, $db) {
    $app->setContext([
        User::USER_ID => $userId
    ]);
    (new Handler\Api\Task\Tasks($app, $db))->process();
});

$app->map('/users/:user_id/tasks/:task_id', function ($userId, $taskId) use ($app, $db) {
    $app->setContext([
        User::USER_ID => $userId,
        'task_id' => $taskId
    ]);
    (new Handler\Api\Task\Task($app, $db))->process();
});

$app->run();

?>
