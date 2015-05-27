<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Handler;
use \Tudu\Test\Mock\MockModel;

/**
 * Mock API request handler
 */
final class MockApiHandler extends Handler\API {
    
    protected $context;
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    protected function post() {
        $this->checkResponseAcceptable();
        $this->checkRequestDecodable();
        
        $data = $this->getNormalizedRequestBody(new MockModel(), [
            'name',
            'email'
        ]);
        
        $this->app->setResponseStatus(201);
    }
    
    protected function put() {
        $context = $this->getNormalizedContext([
            'name' => new MockModel()
        ]);
        
        echo $context['name'];
    }
}

?>
