<?php
namespace Tudu\Core\Delegate;

require_once __DIR__.'/App.php';
require_once __DIR__.'/../../../vendor/autoload.php';

/**
 * An interface between Tudu and any application framework. The interface
 * requires various common methods like redirect(), getRequestHeaders(), etc.
 */
class Slim implements App {
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
}
?>
