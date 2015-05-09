<?php
namespace Tudu\Core;

require_once __DIR__.'/Handler.php';

/**
 * Request handler with authentication.
 */
abstract class AuthHandler extends Handler {
    
    /**
     * Accept a successfully authenticated request.
     */
    protected function acceptAuthentication() {
        $this->delegate->pass();
    }
    
    /**
     * Reject a request that failed authentication.
     */
    protected function rejectAuthentication() {
        $this->delegate->setResponseHeaders(['WWW-Authenticate' => 'tudu realm="api"']);
        $this->delegate->setResponseStatus(401);
        $this->delegate->send();
    }
    
    /**
     * Authenticate the request.
     * 
     * @return bool TRUE if authentication succeeded, FALSE otherwise.
     */
    abstract protected function authenticate();
    
    final public function process() {
        if ($this->authenticate()) {
            $this->acceptAuthentication();
        } else {
            $this->rejectAuthentication();
        }
    }
}
    
?>
