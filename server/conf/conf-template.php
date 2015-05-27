<?php
namespace Tudu\Conf;

use \Psr\Log\LogLevel;

/**
 * This is a template configuration file. To get started, duplicate this file,
 * name it "Conf.php", and adjust the constants below as needed.
 */
class Conf
{
    const DB_HOST = /* TODO */;
    const DB_NAME = /* TODO */;
    const DB_USERNAME = /* TODO */;
    const DB_PASSWORD = /* TODO */;
    
    const LOG_PATH = __DIR__.'/../../logs';
    const LOG_LEVEL = LogLevel::DEBUG;
    
    const AUTHENTICATION_REALM = 'TuduAPI';
    const ACCESS_TOKEN_TTL = '1 year';
}

?>
