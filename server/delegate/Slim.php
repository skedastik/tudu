<?php
namespace Tudu\Delegate;

use \Tudu\Core\Delegate\App;

/**
 * Slim app delegate for Slim 2.6.*
 */
final class Slim implements App {
    protected $slim;
    
    /**
     * Constructor.
     * 
     * @param \Slim\Slim $slim A Slim app instance.
     */
    public function __construct(\Slim\Slim $slim) {
        $this->slim = $slim;
    }
    
    public function redirect($url, $status = 302) {
        return $this->slim->redirect($url, $status);
    }
    
    public function getRequestMethod() {
        return $this->slim->request->getMethod();
    }
    
    public function getRequestHeaders() {
        return $this->slim->request->headers;
    }
    
    public function setResponseHeaders($headers) {
        $this->slim->response->headers->replace($headers);
    }
    
    public function setResponseStatus($status) {
        $this->slim->response->setStatus($status);
    }
    
    public function send() {
        $this->slim->stop();
    }
    
    public function map($route, $callback, ...$methods) {
        if (empty($methods)) {
            $methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'];
        }
        $this->slim->map($route, $callback)->via(...$methods);
    }
    
    public function pass() {
        $this->slim->pass();
    }
}
?>
