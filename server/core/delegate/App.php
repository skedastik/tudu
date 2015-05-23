<?php
namespace Tudu\Core\Delegate;

/**
 * An interface between Tudu and any application framework. The interface
 * describes a contract for various common methods.
 */
interface App {
    /**
     * Immediately send redirect headers.
     * 
     * @param string $url Redirect URL.
     * @param int $status Redirect status code.
     */
    public function redirect($url, $status);
    
    /**
     * Get the HTTP request method.
     */
    public function getRequestMethod();
    
    /**
     * Get all request headers as an associative-array-like object.
     */
    public function getRequestHeaders();
    
    /**
     * Get request body.
     */
    public function getRequestBody();
    
    /**
     * Get request IP.
     */
    public function getRequestIp();
    
    /**
     * Set the specified response headers.
     * 
     * @param array $headers Associative array of HTTP header key/value pairs.
     */
    public function setResponseHeaders($headers);
    
    /**
     * Set the response status code.
     * 
     * @param int $status HTTP response status code.
     */
    public function setResponseStatus($status);
    
    /**
     * Immediately send an HTTP response as currently formed and end processing.
     */
    public function send();
    
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
    public function map($route, $callback, ...$methods);
    
    /**
     * Immediately halt processing and pass control to the next applicable
     * router callback.
     */
    public function pass();
    
    /**
     * Run the application.
     */
    public function run();
}
?>
