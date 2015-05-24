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
    
    private $encoder;
    
    public function __construct() {
        $this->encoder = null;
    }
    
    /**
     * Set HTTP encoder.
     * 
     * The encoder can be used to encode and decode HTTP request and response
     * entities.
     * 
     * @param \Tudu\Core\Data\Encoder\Encoder $encoder
     */
    public function setEncoder(Encoder $encoder) {
        $this->encoder = $encoder;
    }
    
    /**
     * Get HTTP encoder.
     * 
     * @return \Tudu\Core\Data\Encoder\Encoder|null Encoder, or NULL if app does
     * not have an encoder.
     */
    public function getEncoder() {
        return $this->encoder;
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
