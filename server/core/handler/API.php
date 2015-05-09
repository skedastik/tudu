<?php
namespace Tudu\Core\Handler;

require_once __DIR__.'/Handler.php';

/**
 * Request handler base class for all API endpoints. This class can be
 * instantiated if you need an API handler that simply rejects all HTTP methods
 * except for OPTIONS.
 */
abstract class API extends Handler {
    
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
    protected function getAllowedMethods() {
        return 'OPTIONS';
    }
    
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
    
    final public function process() {
        // invoke method corresponding to HTTP request method
        $this->{strtolower($this->delegate->getRequestMethod())}();
    }
}

?>
