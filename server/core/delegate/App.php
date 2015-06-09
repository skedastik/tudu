<?php
namespace Tudu\Core\Delegate;

use \Tudu\Core\Encoder\Encoder;

/**
 * An interface between Tudu and any application framework.
 * 
 * The class describes a contract for various common methods and also adds some
 * of its own functionality.
 */
abstract class App {
    
    private $encoders;
    private $context;
    
    public function __construct() {
        $this->encoders = [];
        $this->context = [];
    }
    
    /**
     * Add an encoder.
     * 
     * The encoder may be used to encode and decode HTTP request and response
     * entities.
     * 
     * @param \Tudu\Core\Data\Encoder\Encoder $encoder
     */
    public function addEncoder(Encoder $encoder) {
        $this->encoders[] = $encoder;
        return $this;
    }
    
    /**
     * Get an encoder.
     * 
     * You may optionally request an encoder for a given media type or set of
     * media types.
     * 
     * @param string $mediaType (optional) Encoder media type(s). Use a comma-
     * delimited list to specify multiple. The first supported media type will
     * be used. If no media type is specified, or media type is NULL, the
     * default encoder is returned (the first encoder added).
     * @return \Tudu\Core\Data\Encoder\Encoder|null Encoder, or NULL if app does
     * not support the specified media types.
     */
    public function getEncoder($mediaType = null) {
        if (is_null($mediaType)) {
            return isset($this->encoders[0]) ? $this->encoders[0] : null;
        }
        $types = explode(',', $mediaType);
        foreach ($types as $type) {
            foreach ($this->encoders as $encoder) {
                if ($encoder->supportsMediaType($type)) {
                    return $encoder;
                }
            }
        }
        return null;
    }
    
    /**
     * Get an array of supported content types.
     * 
     * Each encoder added to the app supports a single content type.
     * 
     * @return array Array of media type strings.
     */
    public function getSupportedContentTypes() {
        return array_map(function ($encoder) {
            return $encoder->getMediaType();
        }, $this->encoders);
    }
    
    /**
     * Set application context data.
     * 
     * @param array $data Key/value array of data with which to update app
     * context.
     */
    public function setContext($data) {
        $this->context = array_merge($this->context, $data);
    }
    
    /**
     * Set application context data.
     * 
     * @param string $key (optional) Get value under specific context key.
     * @return array Key/value array of context data.
     */
    public function getContext($key = null) {
        return is_null($key) ? $this->context : $this->context[$key];
    }
    
    /**
     * Immediately send redirect headers.
     * 
     * @param string $url Redirect URL.
     * @param int $status Redirect status code.
     */
    abstract public function redirect($url, $status);
    
    /**
     * Get the HTTP request method.
     * 
     * @return string The request method (e.g., 'GET', 'POST')
     */
    abstract public function getRequestMethod();
    
    /**
     * Get the specified request header (e.g., 'Accept', 'Content-Type').
     */
    abstract public function getRequestHeader($header);
    
    /**
     * Get request body.
     */
    abstract public function getRequestBody();
    
    /**
     * Get request IP.
     */
    abstract public function getRequestIp();
    
    /**
     * Get the specified response header (e.g., 'Accept', 'Content-Type').
     */
    abstract public function getResponseHeader($header);
    
    /**
     * Set the specified response headers.
     * 
     * @param array $headers Associative array of HTTP header key/value pairs.
     */
    abstract public function setResponseHeaders($headers);
    
    /**
     * Set the response status code.
     * 
     * @param int $status HTTP response status code.
     */
    abstract public function setResponseStatus($status);
    
    /**
     * Get full URI of requested resource.
     * 
     * In "http://www.example.com/path/to/resource", the resource URI is
     * "/path/to/resource".
     */
    abstract public function getRequestUri();
    
    /**
     * Get request scheme.
     * 
     * In "http://www.example.com/path/to/resource", the scheme is "http".
     */
    abstract public function getRequestScheme();
    
    /**
     * Get request host.
     * 
     * In "http://www.example.com/path/to/resource", the host is
     * "example.com".
     */
    abstract public function getRequestHost();
    
    /**
     * Get the full request URL inlcuding scheme, host, and URI, e.g.:
     * "http://www.example.com/path/to/resource"
     */
    public function getFullRequestUrl() {
        return $this->app->getRequestScheme().'://'.$this->app->getRequestHost().$this->app->getRequestUri();
    }
    
    /**
     * Immediately send an HTTP response as currently formed and end processing.
     */
    abstract public function send();
    
    /**
     * Request router.
     * 
     * Map a request URI to a callback for the given HTTP request methods. The
     * callback will be invoked when a matching URI is requested using one of
     * the listed HTTP methods. Should multiple callbacks apply to a given
     * route, the first callback registered is invoked. To immediately halt
     * processing and invoke the next applicable callback, use pass().
     * 
     * @param string $route A resource URI, possibly parameterized.
     * @param callable $callback A callback function, possibly taking arguments
     * corresponding to parameters in the route string.
     * @param mixed $methods Any number of string arguments, each an HTTP
     * request method. If no method arguments are supplied, the route applies to
     * all methods. The following methods are supported: GET, POST, PUT, DELETE,
     * OPTIONS, PATCH, HEAD.
     */
    abstract public function map($route, $callback, ...$methods);
    
    /**
     * Shorthand GET request router.
     */
    abstract public function get($route, $callback);
    
    /**
     * Shorthand POST request router.
     */
    abstract public function post($route, $callback);
    
    /**
     * Shorthand PUT request router.
     */
    abstract public function put($route, $callback);
    
    /**
     * Shorthand DELETE request router.
     */
    abstract public function delete($route, $callback);
    
    /**
     * Shorthand PATCH request router.
     */
    abstract public function patch($route, $callback);
    
    /**
     * Immediately halt processing and pass control to the next applicable
     * router callback.
     */
    abstract public function pass();
    
    /**
     * Run the application.
     */
    abstract public function run();
}
?>
