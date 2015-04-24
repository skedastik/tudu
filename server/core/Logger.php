<?php
namespace Tudu\Core;

require __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../conf/conf.php';

use \Katzgrau\KLogger\Logger as KLogger;
use \Tudu\Conf;

class Logger {
    private static $instance = NULL;

    private function __construct() {}
        
    /**
     * Inject an existing instance of a PSR3-compliant logger.
     * @param \Psr\Log\LoggerInterface $logger An instance of a PSR3-compliant logger implementation.
     */
    public static function setInstance($logger) {
        if (self::$instance !== NULL) {
            throw new Exception('A logger has already been instantiated.');
        }
        self::$instance = $logger;
        return self::$instance;
    }
    
    /**
     * Get PSR3-compliant singleton instance.
     * @return \Katzgrau\KLogger\Logger
     */
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new KLogger(Conf\LOG_PATH, Conf\LOG_LEVEL);
        }
        return self::$instance;
    }
}
?>