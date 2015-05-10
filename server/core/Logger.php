<?php
/**
 * A PSR3-compliant logger singleton. Get or set an instance and proceed to log!
 * By default, getInstance() returns an instance of KLogger.
 * 
 * EXAMPLE
 * 
 *    // get KLogger instance
 *    $logger = \Tudu\Core\Logger::getInstance();
 *    
 *    $data = ['data' => 'this will be dumped in the log'];
 *        
 *    $logger->debug('message', $data);
 *    $logger->info('message', $data);
 *    $logger->notice('message', $data);
 *    $logger->warning('message', $data);
 *    $logger->error('message', $data);
 *    $logger->critical('message', $data);
 *    $logger->alert('message', $data);
 *    $logger->emergency('message', $data);
 *    
 */

namespace Tudu\Core;

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../conf/conf.php';

use \Katzgrau\KLogger\Logger as KLogger;
use \Tudu\Conf;

class Logger {
    
    private static $instance = NULL;

    private function __construct() {}
        
    /**
     * Inject an existing instance of a PSR3-compliant logger.
     * @param \Psr\Log\LoggerInterface $logger An instance of a PSR3-compliant
     * logger implementation.
     */
    public static function setInstance(\Psr\Log\LoggerInterface $logger) {
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
