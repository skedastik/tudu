<?php
namespace Tudu\Core;

require_once __DIR__.'/Handler.php';

/**
 * Request handler with authentication.
 */
abstract class AuthHandler extends Handler {
    
    /**
     * Process a successfully authenticated request.
     */
    abstract protected function acceptAuthentication();
    
    /**
     * Reject a request that failed authentication.
     */
    abstract protected function rejectAuthentication();
    
    /**
     * Authenticate the request.
     * 
     * @return bool TRUE if authentication succeeded, FALSE otherwise.
     */
    private function authenticate() {
        if (isset($this->context['headers']['Authorization'])) {
            /* TODO: Perform HMAC-inspired authentication. */
        }
        return true;
    }
    
    final public function process() {
        if ($this->authenticate()) {
            $this->acceptAuthentication();
        } else {
            $this->rejectAuthentication();
        }
    }
}
    
?>
