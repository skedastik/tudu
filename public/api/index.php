<?php

require_once __DIR__.'/../../server/autoload.php';

use \Tudu\Conf\Conf;
use \Tudu\Core\Logger;
use \Tudu\Handler;
use \Tudu\Core\Handler\Auth\Auth as AuthHandler;
use \Tudu\Handler\Auth\Contract\BasicAuthentication;
use \Tudu\Handler\Auth\Contract\TuduAuthentication;
use \Tudu\Handler\Auth\Contract\TuduAuthorization;
use \Tudu\Data\Model\User;
use \Tudu\Data\Model\Task;

Logger::setInstance(new \Katzgrau\KLogger\Logger(Conf::LOG_PATH, Conf::LOG_LEVEL));
User::setPasswordDelegate(new \Tudu\Delegate\PHPass());

$db = new \Tudu\Core\Database\PgSQLConnection([
    'host'     => Conf::DB_HOST,
    'database' => Conf::DB_NAME,
    'username' => Conf::DB_USERNAME,
    'password' => Conf::DB_PASSWORD
]);

$app = new \Tudu\Delegate\Slim(new \Slim\Slim());
$app->addEncoder(new \Tudu\Core\Encoder\JSON());

$basicAuthentication = new BasicAuthentication($db);

// User URIs -------------------------------------------------------------------

$app->map('/users/', function () use ($app, $db) {
    (new Handler\Api\User\Users(
        $app,
        $db
    ))->run();
});

$app->post('/signin', function () use ($app, $db, $basicAuthentication) {
    (new AuthHandler(
        $app,
        $db,
        $basicAuthentication,
        new TuduAuthorization()
    ))->run();
});

$app->map('/signin', function () use ($app, $db) {
    (new Handler\Api\User\Signin(
        $app,
        $db
    ))->run();
});

$app->put('/users/:user_id', function ($userId) use ($app, $db, $basicAuthentication) {
    (new AuthHandler(
        $app,
        $db,
        $basicAuthentication,
        new TuduAuthorization($userId)
    ))->run();
});

$app->map('/users/:user_id', function ($userId) use ($app, $db) {
    $app->setContext([
        User::USER_ID => $userId
    ]);
    (new Handler\Api\User\User($app, $db))->run();
});

$app->map('/users/:user_id/confirm', function ($userId) use ($app, $db) {
    $app->setContext([
        User::USER_ID => $userId
    ]);
    (new Handler\Api\User\Confirm($app, $db))->run();
});

// Task URIs -------------------------------------------------------------------

$app->map('/users/:user_id/tasks/(:task_id)', function ($userId) use ($app, $db) {
    (new AuthHandler(
        $app,
        $db,
        new TuduAuthentication($app, $db, $userId),
        new TuduAuthorization($userId)
    ))->run();
});

$app->map('/users/:user_id/tasks/', function ($userId) use ($app, $db) {
    $app->setContext([
        User::USER_ID => $userId
    ]);
    (new Handler\Api\Task\Tasks($app, $db))->run();
});

$app->map('/users/:user_id/tasks/:task_id', function ($userId, $taskId) use ($app, $db) {
    $app->setContext([
        User::USER_ID => $userId,
        Task::TASK_ID => $taskId
    ]);
    (new Handler\Api\Task\Task($app, $db))->run();
});

$app->run();

?>
