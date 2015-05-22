<?php
namespace Tudu\Core\Handler;

use \Tudu\Core\Arrayable;
use \Tudu\Core\Error;
use \Tudu\Core\Data\Model\Model;

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
    protected function post(Model $model) {
        $this->rejectMethod();
    }
    
    /**
     * Handle PUT requests on this endpoint. Override for custom behavior.
     */
    protected function put(Model $model) {
        $this->rejectMethod();
    }
    
    /**
     * Handle PATCH requests on this endpoint. Override for custom behavior.
     */
    protected function patch(Model $model) {
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
     * Get an empty Model object. Override this.
     * 
     * Subclasses should return an empty instance of a Model subclass. The Model
     * subclass should correspond to the type of resource described by the API
     * endpoint.
     */
    abstract protected function getModel();
    
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
        if (strpos($methods, 'OPTIONS') === false) {
            return empty($methods) ? 'OPTIONS' : $methods.', OPTIONS';
        }
        return $methods;
    }
    
    /**
     * Default behavior for rejecting unsupported request methods.
     */
    protected function rejectMethod() {
        $this->delegate->setResponseHeaders(['Allow' => $this->_getAllowedMethods()]);
        $this->delegate->setResponseStatus(405);
        $this->delegate->send();
    }
    
    /**
     * If request method is POST, PUT, or PATCH, the request body is
     * automatically decoded and transformed into a normalized Model object. If
     * validation fails, processing halts and an error response is automatically
     * generated.
     */
    final public function process() {
        $method = strtolower($this->delegate->getRequestMethod());
        $model = null;
        
        if ($method == 'post' || $method == 'put' || $method == 'patch') {
            // TODO: Do not assume JSON content type.
            $data = json_decode($this->delegate->getRequestBody(), true);
            $model = $this->getModel()->fromArray($data);
            $errors = $model->normalize();
            if (!is_null($errors)) {
                $this->renderError(Error::Validation(null, $errors, 400));
                return;
            }
        }
        
        // invoke method corresponding to HTTP request method
        $this->{$method}($model);
    }
    
    /**
     * Render error response.
     * 
     * This method sets the HTTP status code and translates an error object into
     * a response body.
     * 
     * @param \Tudu\Core\Error $error Error object.
     */
    final protected function renderError($error) {
        $statusCode = $data->getHttpStatusCode();
        $this->delegate->setResponseStatus(is_null($statusCode) ? 400 : $statusCode);
        $this->renderBody($data->asArray());
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
