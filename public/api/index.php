<?php

require_once __DIR__.'/../../server/tudu_autoload.php';

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

// User URIs -------------------------------------------------------------------

$delegate->map('/users/:user_id', function ($user_id) use ($delegate, $db) {
    (new Core\Handler\Auth\Basic($delegate, $db, [
        'user_id' => $user_id
    ]))->process();
}, 'PUT');

$delegate->map('/users/(:user_id)', function ($user_id = null) use ($delegate, $db) {
    (new Handler\Api\User($delegate, $db, [
        'user_id' => $user_id
    ]))->process();
});

// Task URIs -------------------------------------------------------------------

$delegate->map('/users/:user_id/tasks/(:task_id)', function ($user_id, $task_id = null) use ($delegate, $db) {
    (new Core\Handler\Auth\HMAC($delegate, $db, [
        'user_id' => $user_id,
        'task_id' => $task_id
    ]))->process();
});

$delegate->map('/users/:user_id/tasks/(:task_id)', function ($user_id, $task_id = null) use ($delegate, $db) {
    (new Handler\Api\Task($delegate, $db, [
        'user_id' => $user_id,
        'task_id' => $task_id
    ]))->process();
});

$app->run();
?>
