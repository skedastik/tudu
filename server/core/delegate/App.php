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
     * Get all request headers as an associative-array-like object.
     */
    public function getRequestHeaders();
    
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
}
?>
