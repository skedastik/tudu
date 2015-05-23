<?php
namespace Tudu\Core\Handler\Auth;

require_once __DIR__.'/../Handler.php';

/**
 * Request handler with authentication.
 */
abstract class Auth extends \Tudu\Core\Handler\Handler {
    
    /**
     * Accept a successfully authenticated request.
     */
    protected function acceptAuthentication() {
        $this->app->pass();
    }
    
    /**
     * Reject a request that failed authentication.
     */
    protected function rejectAuthentication() {
        /**
         * TODO: Produce the authentication scheme via an overridable method
         * rather than hard-coding it.
         */
        $this->app->setResponseHeaders(['WWW-Authenticate' => 'tudu realm="api"']);
        $this->app->setResponseStatus(401);
        $this->app->send();
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
