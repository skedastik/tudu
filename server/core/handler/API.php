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
        $this->app->setResponseHeaders([
            'Allow' => $this->_getAllowedMethods()
        ]);
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
     * Default behavior for rejecting unsupported request methods.
     */
    protected function rejectMethod() {
        $this->app->setResponseHeaders([
            'Allow' => $this->_getAllowedMethods()
        ]);
        $this->app->setResponseStatus(405);
        $this->app->send();
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
     * Ensure that our application is capable of encoding its response payload
     * in a format specified by the request's "Accept" header.
     * 
     * This function may change the response "Content-Type" header.
     * 
     * If none of the media types specified by the "Accept" header is supported,
     * halt processing immediately and send an error response.
     * 
     * If no "Accept" header is provided, we are free to use any media type.
     */
    private function checkResponseAcceptable() {
        $accept = $this->app->getRequestHeader('Accept');
        if (is_null($accept)) {
            // no accept header provided, so stick with the default media type
            return;
        }
        
        // set response content type to first supported, accepted media type
        $encoder = $this->app->getEncoder();
        $acceptedMediaTypes = explode(',', $accept);
        foreach ($acceptedMediaTypes as $mediaType) {
            $supportedMediaType = $encoder->supportsMediaType($mediaType);
            if ($supportedMediaType) {
                $this->app->setResponseHeaders([
                    'Content-Type' => $supportedMediaType
                ]);
                return;
            }
        }
        
        // no supported media types are accepted, so send an error response
        $description = 'Accepted media types are not supported. See context for a list of supported media types.';
        $this->sendError(Error::Generic($description, $encoder->getSupportedMediaTypes(), 406));
    }
    
    /**
     * Check that the request's content type can be decoded.
     * 
     * If request's content type is not supported, halt processing immediately
     * and send an error response. This is only meaningful for requests with
     * payloads (i.e., POST, PUT, and PATCH).
     */
    private function checkRequestDecodable() {
        $method = $this->app->getRequestMethod();
        if ($method == 'POST' || $method == 'PUT' || $method == 'PATCH') {
            $requestContentType = $this->app->getRequestHeader('Content-Type');
            $encoder = $this->app->getEncoder();
            if (!$encoder->supportsMediaType($requestContentType)) {
                $description = 'Request content type not supported. See context for a list of supported media types.';
                $this->sendError(Error::Generic($description, $encoder->getSupportedMediaTypes(), 415));
            }
        }
    }
    
    final public function process() {
        /**
         * TODO: This base class, admittedly, has too many responsibilities. To
         * date, it:
         * 
         * - Performs content negotation (even simplified content negotiation
         *   adds a huge amount of complexity).
         * - Performs automatic validation of request entities.
         * - Dispatches methods based on the HTTP request method.
         * 
         * This is good in that almost all of the complexity is hidden from API
         * request handler subclasses, but bad in the sense that this class will
         * become increasingly difficult to maintain. A thoughtful refactor is
         * in order.
         */
        
        $this->app->setResponseHeaders([
            // default to the first supported media type
            'Content-Type' => $this->app->getEncoder()->getSupportedMediaTypes()[0]
        ]);
        
        $this->checkResponseAcceptable();
        $this->checkRequestDecodable();
        
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
     * @param array $requiredProperties Array of normalized data.
     * @return array Key/value array of normalized model data.
     */
    protected function decodeRequestBody($requiredProperties) {
        $mediaType = $this->app->getRequestHeader('Content-Type');
        $requestBody = $this->app->getRequestBody();
        $data = $this->app->getEncoder()->decode($requestBody, $mediaType);
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
            $mediaType = $this->app->getResponseHeader('Content-Type');
            $data = $this->app->getEncoder()->encode($data, $mediaType);
        }
        echo $data;
    }
}

?>
