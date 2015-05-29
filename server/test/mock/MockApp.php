<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Handler\Handler;
use \Tudu\Core\Delegate\App;
use \Tudu\Core\Encoder;

final class MockApp extends App {
    
    private $requestHeaders;
    private $requestMethod;
    private $requestBody;
    private $responseHeaders;
    private $responseStatus;
    private $handler;
    
    public function __construct() {
        parent::__construct();
        $this->requestHeaders = [];
        $this->requestMethod = null;
        $this->requestBody = null;
        $this->responseHeaders = [];
        $this->responseStatus = null;
        $this->handler = null;
    }
    
    public function redirect($url, $status) {}
    
    public function setRequestMethod($method) {
        $this->requestMethod = $method;
    }
    
    public function getRequestMethod() {
        return $this->requestMethod;
    }
    
    public function setRequestHeader($header, $value) {
        $this->requestHeaders[$header] = $value;
    }
    
    public function getRequestHeader($header) {
        return isset($this->requestHeaders[$header]) ? $this->requestHeaders[$header] : null;
    }
    
    public function setRequestBody($body) {
        $this->requestBody = $body;
    }
    
    public function getRequestBody() {
        return $this->requestBody;
    }
    
    public function getRequestIp() {
        return '127.0.0.1';
    }
    
    public function getResponseHeader($header) {
        return isset($this->responseHeaders[$header]) ? $this->responseHeaders[$header] : null;
    }
    
    public function setResponseHeaders($headers) {
        $this->responseHeaders = array_merge($this->responseHeaders, $headers);
    }
    
    public function setResponseStatus($status) {
        $this->responseStatus = $status;
    }
    
    public function getResponseStatus() {
        return $this->responseStatus;
    }
    
    /**
     * Use this function to unit test handlers. The handler's `process` method
     * will be invoked from MockApp::run().
     */
    public function setHandler(Handler $handler) {
        $this->handler = $handler;
    }
    
    public function send() {
        throw new MockException('MockApp::send()');
    }
    
    public function map($route, $callback, ...$methods) {}
    public function get($route, $callback) {}
    public function post($route, $callback) {}
    public function put($route, $callback) {}
    public function delete($route, $callback) {}
    public function patch($route, $callback) {}
    
    public function pass() {
        $this->setResponseStatus(200);
        throw new MockException('MockApp::pass()');
    }
    
    public function run() {
        try {
            $this->handler->process();
        } catch (MockException $e) {
            // swallow exception
        }
    }
}
?>
