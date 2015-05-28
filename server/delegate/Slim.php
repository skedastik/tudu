<?php
namespace Tudu\Delegate;

use \Tudu\Core\Delegate\App;

/**
 * Slim app delegate for Slim 2.6.*
 */
final class Slim extends App {
    protected $slim;
    
    /**
     * Constructor.
     * 
     * @param \Slim\Slim $slim A Slim app instance.
     */
    public function __construct(\Slim\Slim $slim) {
        parent::__construct();
        $this->slim = $slim;
    }
    
    public function redirect($url, $status = 302) {
        return $this->slim->redirect($url, $status);
    }
    
    public function getRequestMethod() {
        return $this->slim->request->getMethod();
    }
    
    public function getRequestHeader($header) {
        return $this->slim->request->headers->get($header);
    }
    
    public function getRequestBody() {
        return $this->slim->request->getBody();
    }
    
    public function getRequestIp() {
        return $this->slim->request->getIp();
    }
    
    public function getResponseHeader($header) {
        return $this->slim->response->headers->get($header);
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
            $methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH', 'HEAD'];
        }
        $this->slim->map($route, $callback)->via(...$methods);
    }
    
    public function get($route, $callback) {
        $this->slim->get($route, $callback);
    }
    
    public function post($route, $callback) {
        $this->slim->post($route, $callback);
    }
    
    public function put($route, $callback) {
        $this->slim->put($route, $callback);
    }
    
    public function delete($route, $callback) {
        $this->slim->delete($route, $callback);
    }
    
    public function patch($route, $callback) {
        $this->slim->patch($route, $callback);
    }
    
    public function pass() {
        $this->slim->pass();
    }
    
    public function run() {
        $this->slim->run();
    }
}
?>
