<?php
namespace Tudu\Handler\Api;

require_once __DIR__.'/../../core/AuthHandler.php';

/**
 * Request handler base class for all API endpoints.
 */
abstract class APIHandler extends \Tudu\Core\AuthHandler {
    
    /**
     * Handle GET requests on this endpoint. Override for custom behavior.
     */
    protected function get() {
        $this->rejectMethod();
    }
    
    /**
     * Handle POST requests on this endpoint. Override for custom behavior.
     */
    protected function post() {
        $this->rejectMethod();
    }
    
    /**
     * Handle PUT requests on this endpoint. Override for custom behavior.
     */
    protected function put() {
        $this->rejectMethod();
    }
    
    /**
     * Handle PATCH requests on this endpoint. Override for custom behavior.
     */
    protected function patch() {
        $this->rejectMethod();
    }
    
    /**
     * Handle DELETE requests on this endpoint. Override for custom behavior.
     */
    protected function delete() {
        $this->rejectMethod();
    }
    
    /**
     * Handle OPTIONS requests on this endpoint.
     */
    final private function options() {
        /* TODO: Provide a more descriptive response */
        $this->delegate->setResponseHeaders(['Allow' => $this->_getAllowedMethods()]);
        $this->delegate->setResponseStatus(200);
        $this->delegate->send();
    }
    
    /**
     * Return a comma-delimited string of HTTP methods allowed on this endpoint.
     * This method MUST be overridden by subclasses. You need not specify the
     * OPTIONS method as this is handled automatically.
     * 
     * @return string Example: "GET, PUT, POST"
     */
    abstract protected function getAllowedMethods();
    
    private function _getAllowedMethods() {
        $methods = strtoupper($this->getAllowedMethods());
        return strpos($methods, 'OPTIONS') === false ? (empty($methods) ? 'OPTIONS' : $methods.', OPTIONS') : $methods;
    }
    
    /**
     * Default behavior for rejecting unsupported request methods.
     */
    protected function rejectMethod() {
        $this->delegate->setResponseHeaders(['Allow' => $this->_getAllowedMethods()]);
        $this->delegate->setResponseStatus(405);
        $this->delegate->send();
    }
    
    final protected function acceptAuthentication() {
        $this->{strtolower($this->delegate->getRequestMethod())}();
    }
    
    protected function rejectAuthentication() {
        $this->delegate->setResponseHeaders(['WWW-Authenticate' => 'tudu realm="api"']);
        $this->delegate->setResponseStatus(401);
        $this->delegate->send();
    }
}

?>
