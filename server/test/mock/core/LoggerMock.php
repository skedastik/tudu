<?php
namespace Tudu\Test\Mock\Core;

require_once __DIR__.'/../../../../vendor/autoload.php';

use Psr\Log\AbstractLogger;

/**
 * Mock Logger
 */
class LoggerMock extends AbstractLogger {
    protected $log;
    
    public function __construct() {
        $this->log = [];
    }
    
    public function log($level, $message, array $context = array()) {
        $this->log[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];
    }
    
    /**
     * Check if mock logger logged the given message at the given level with
     * the given context.
     * 
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return bool Returns TRUE if matching entry was found, FALSE otherwise.
     */
    public function didLog($level, $message, array $context = array()) {
        $inputEntry = serialize([
            'level' => $level,
            'message' => $message,
            'context' => $context
        ]);
        foreach ($this->log as $entry) {
            if ($inputEntry === serialize($entry)) {
                return true;
            }
        }
        return false;
    }
}
?>
