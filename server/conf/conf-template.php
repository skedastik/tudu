<?php
/**
 * This is a configuration file template. To get started, duplicate it and name 
 * it "conf.php".
 */

namespace Tudu\Conf;

use \Psr\Log\LogLevel;

// database

define('Tudu\Conf\DB_HOST',     /* TODO */);
define('Tudu\Conf\DB_NAME',     /* TODO */);
define('Tudu\Conf\DB_USERNAME', /* TODO */);
define('Tudu\Conf\DB_PASSWORD', /* TODO */);

// logger

define('Tudu\Conf\LOG_PATH', __DIR__.'/../../logs');
define('Tudu\Conf\LOG_LEVEL', LogLevel::DEBUG);

?>
