<?php
namespace Tudu\Core\Handler;

use \Tudu\Core\Database\DbConnection;
use \Tudu\Core\Delegate;
use \Tudu\Core\Error;
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
        $this->app->send();
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
        $this->app->send();
    }
    
    /**
     * Set "Allow" header.
     */
    private function setAllowHeader() {
        $this->app->setResponseHeaders([
            'Allow' => $this->getAllowedMethods()
        ]);
    }
    
    final public function process() {
        $method = strtolower($this->app->getRequestMethod());
        // invoke method corresponding to HTTP request method
        $this->{$method}();
    }
    
    /**
     * Decode the request body into normalized model data.
     * 
     * If the request body is valid, its data is normalized and returned as a
     * key/value array.
     * 
     * The request body is considered invalid if the resource it represents is
     * missing any of the given properties, or if the properties themselves fail
     * to validate. In such cases, processing halts and an error response is
     * automatically generated.
     * 
     * @param \Tudu\Core\Data\Model $model Model for normalizing data.
     * @param array $requiredProperties Array of properties.
     * @return array Key/value array of normalized model data.
     */
    protected function getNormalizedRequestBody($model, $requiredProperties) {
        $data = $this->decodeRequestBody();
        $model->fromArray($data);
        
        if (!$model->hasProperties($requiredProperties)) {
            $missingProperties = array_values(array_diff($requiredProperties, array_keys($data)));
            $this->sendError(Error::Generic('Request body is missing listed properties.', $missingProperties, 400));
        }
        
        $errors = $model->normalize();
        if (!is_null($errors)) {
            $this->sendError(Error::Validation(null, $errors, 400));
        }
        
        return $model->asArray();
    }
    
    /**
     * Similar to `getNormalizedRequestBody`, but for application context data.
     * 
     * If application context data is valid, it is normalized and returned as
     * another key/value array.
     * 
     * Context data is considered invalid if any of its properties fail to
     * validate by a corresponding Model object.
     * 
     * @param array $propertyNormalizers Key/value array where keys are
     * properties and values are Model objects used to normalize the
     * corresponding properties.
     * @return array Key/value array of normalized context data.
     */
    protected function getNormalizedContext($propertyNormalizers) {
        $appContext = $this->app->getContext();
        $context = [];
        foreach ($propertyNormalizers as $property => $model) {
            $model->fromArray([
                $property => $appContext[$property]
            ]);
            $errors = $model->normalize();
            if (!is_null($errors)) {
                $this->sendError(Error::Validation(null, $errors, 400));
            }
            $context[$property] = $model->get($property);
        }
        return $context;
    }
}

?>
