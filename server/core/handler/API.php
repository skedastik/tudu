<?php
namespace Tudu\Core\Handler;

use \Tudu\Core\Arrayable;
use \Tudu\Core\Error;
use \Tudu\Core\Data\Model;
use \Tudu\Core\MediaType;

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
     * Handle HEAD requests on this endpoint. Override for custom behavior.
     */
    protected function head() {
        $this->rejectMethod();
    }
    
    /**
     * Handle OPTIONS requests on this endpoint.
     */
    final private function options() {
        /* TODO: Provide a more descriptive response */
        $this->app->setResponseHeaders(['Allow' => $this->_getAllowedMethods()]);
        $this->app->setResponseStatus(200);
        $this->app->send();
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
        if (strpos($methods, 'OPTIONS') === false) {
            return empty($methods) ? 'OPTIONS' : $methods.', OPTIONS';
        }
        return $methods;
    }
    
    /**
     * Get an empty Model object. Override this.
     * 
     * Subclasses should return an empty instance of a Model subclass. The Model
     * subclass should correspond to the type of resource described by the API
     * endpoint.
     */
    abstract protected function getModel();
    
    /**
     * Require a request entity content type.
     * 
     * If the request content type does not match, processing halts immediately
     * and an error response is sent.
     * 
     * @param string $contentTypeString
     */
    protected function requireRequestContentType($contentTypeString) {
        $contentType = new MediaType($contentTypeString);
        $requestContentType = new MediaType($this->app->getRequestHeaders()['Content-Type']);
        if (!$contentType->compare($requestContentType)) {
            $this->sendError(Error::Generic("Request entity media type must be: $contentTypeString", null, 415));
        }
    }
    
    /**
     * Translate the request body into normalized model data.
     * 
     * If the request body is valid, its data is normalized and returned as a
     * key/value array.
     * 
     * The request body is considered invalid if the resource it represents is
     * missing any of the given properties, or if the properties themselves fail
     * to validate. In such cases, processing halts and an error response is
     * automatically generated.
     * 
     * @param array $requiredProperties Array of normalized data.
     */
    protected function translateRequestBody($requiredProperties) {
        $data = json_decode($this->app->getRequestBody(), true);
        if (is_null($data)) {
            $description = 'Badly formatted request body. Expected a resource descriptor with the listed properties.';
            $this->sendError(Error::Generic($description, $requiredProperties, 400));
        }
        
        $model = $this->getModel()->fromArray($data);
        
        if (!$model->hasProperties($requiredProperties)) {
            $missingProperties = array_values(array_diff($requiredProperties, array_keys($data)));
            $this->sendError(Error::Generic('Resource descriptor is missing listed properties.', $missingProperties, 400));
        }
        
        $errors = $model->normalize();
        if (!is_null($errors)) {
            $this->sendError(Error::Validation(null, $errors, 400));
        }
        
        return $model->asArray();
    }
    
    /**
     * Default behavior for rejecting unsupported request methods.
     */
    protected function rejectMethod() {
        $this->app->setResponseHeaders(['Allow' => $this->_getAllowedMethods()]);
        $this->app->setResponseStatus(405);
        $this->app->send();
    }
    
    final public function process() {
        $method = strtolower($this->app->getRequestMethod());
        
        // TODO: Do not assume JSON content type accepted.
        // TODO: Sanitize all output.
        
        $this->app->setResponseHeaders([
            'Content-Type' => 'application/json; charset=utf-8'
        ]);
        
        // invoke method corresponding to HTTP request method
        $this->{$method}();
    }
    
    /**
     * Halt processing immediately and send an error response.
     * 
     * @param \Tudu\Core\Error $error Error object.
     */
    final protected function sendError($error) {
        $statusCode = $error->getHttpStatusCode();
        $this->app->setResponseStatus(is_null($statusCode) ? 400 : $statusCode);
        $this->renderBody($error->asArray());
        $this->app->send();
    }
    
    /**
     * Render response body.
     * 
     * Input data must either be an Arrayable object, an array, or a string. If
     * one of the former, data is encoded as a string before being rendered.
     * 
     * @param mixed $data Either an Arrayable object, an array, or a string.
     */
    final protected function renderBody($data) {
        if ($data instanceof Arrayable) {
            $data = $data->asArray();
        }
        if (is_array($data)) {
            // TODO: Do not assume JSON content type.
            $data = json_encode($data);
        }
        echo $data;
    }
}

?>
