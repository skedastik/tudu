<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../server/core/delegate/Slim.php';
require_once __DIR__.'/../../server/core/data/PgSQLConnection.php';
require_once __DIR__.'/../../server/conf/conf.php';
require_once __DIR__.'/../../server/handler/frontend/FrontpageHandler.php';
require_once __DIR__.'/../../server/handler/frontend/SigninHandler.php';
require_once __DIR__.'/../../server/handler/frontend/SignupHandler.php';

use \Tudu\Core;
use \Tudu\Conf;
use \Tudu\Handler\Frontend;

$db = new Core\Data\PgSQLConnection([
    'host'     => Conf\DB_HOST,
    'database' => Conf\DB_NAME,
    'username' => Conf\DB_USERNAME,
    'password' => Conf\DB_PASSWORD
]);

$app = new \Slim\Slim();
$delegate = new Core\Delegate\Slim($app);

$app->get('/', function () use ($delegate, $db) {
    (new Frontend\FrontpageHandler($delegate, $db))->process();
});

$app->get('/signup/', function () use ($delegate, $db) {
    (new Frontend\SignupHandler($delegate, $db))->process();
});

$app->get('/signin/', function () use ($delegate, $db) {
    (new Frontend\SigninHandler($delegate, $db))->process();
});

$app->get('/activate/', function () use ($delegate, $db) {
    /* TODO */
});

$app->get('/activate/:user_id/', function ($user_id) use ($delegate, $db) {
    /* TODO */
});

$app->get('/account/', function () use ($delegate, $db) {
    /* TODO */
});

$app->run();
?>
