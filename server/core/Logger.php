<?php
namespace Tudu\Core;

use \Tudu\Conf\Conf;
use \Psr\Log\LoggerInterface;

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
class Logger {
    
    private static $instance = null;

    private function __construct() {}
        
    /**
     * Inject an existing instance of a PSR3-compliant logger.
     * @param \Psr\Log\LoggerInterface $logger An instance of a PSR3-compliant
     * logger implementation.
     */
    public static function setInstance(LoggerInterface $logger) {
        if (self::$instance !== null) {
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
        if (self::$instance === null) {
            throw new Exception('A logger instance has not been set.');
        }
        return self::$instance;
    }
}
?>
