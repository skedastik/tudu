<?php
namespace Tudu\Core\Handler;

use \Tudu\Core\Database\DbConnection;
use \Tudu\Core\Delegate;
use \Tudu\Core\Exception;
use \Tudu\Core\Data\Model;
use \Tudu\Core\MediaType;

/**
 * Request handler base class for all API endpoints.
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
     * Handle HEAD requests on this endpoint. Override for custom behavior.
     */
    protected function head() {
        $this->rejectMethod();
    }
    
    /**
     * Handle OPTIONS requests on this endpoint.
     */
    final private function options() {
        $this->setAllowHeader();
        $this->app->setResponseStatus(200);
    }
    
    /**
     * Return a comma-delimited string of HTTP methods allowed on this endpoint.
     * You need not specify the OPTIONS method as this is handled automatically.
     * 
     * @return string Example: "GET, PUT, POST"
     */
    abstract protected function _getAllowedMethods();
    
    final public function getAllowedMethods() {
        $methods = strtoupper($this->_getAllowedMethods());
        if (strpos($methods, 'OPTIONS') === false) {
            return empty($methods) ? 'OPTIONS' : $methods.', OPTIONS';
        }
        return $methods;
    }
    
    /**
     * Default behavior for rejecting unsupported request methods.
     */
    private function rejectMethod() {
        $this->setAllowHeader();
        $this->app->setResponseStatus(405);
    }
    
    /**
     * Set "Allow" header.
     */
    private function setAllowHeader() {
        $this->app->setResponseHeaders([
            'Allow' => $this->getAllowedMethods()
        ]);
    }
    
    final protected function process() {
        $method = strtolower($this->app->getRequestMethod());
        // invoke method corresponding to HTTP request method
        $this->{$method}();
    }
    
    /**
     * Import data from request body and application context into a Model
     * object.
     * 
     * Before being imported into a Model object, body data and application
     * context are merged into a single array with application context taking
     * precedence.
     * 
     * You may optionally specify required properties. If any of these
     * properties are missing from the merged data, a Client exception is
     * thrown.
     * 
     * @param \Tudu\Core\Data\Model $model
     * @param array $requiredProperties
     * @return \Tudu\Core\Data\Model
     */
    protected function importRequestData($model, $requiredProperties) {
        $bodyData = $this->decodeRequestBody();
        $appContext = $this->app->getContext();
        $data = array_merge($bodyData, $appContext);
        $model->fromArray($data);
        if (!$model->hasProperties($requiredProperties)) {
            $missingProperties = array_values(array_diff($requiredProperties, array_keys($data)));
            throw new Exception\Client('Request body is missing listed properties.', $missingProperties, 400);
        }
        return $model;
    }
}

?>
