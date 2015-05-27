<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Handler;
use \Tudu\Test\Mock\MockModel;

/**
 * Mock API request handler
 */
final class MockApiHandler extends Handler\API {
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    protected function getModel() {
        return new MockModel();
    }
    
    protected function post() {
        $this->checkResponseAcceptable();
        $this->checkRequestDecodable();
        
        $data = $this->getNormalizedRequestBody([
            'name',
            'email'
        ]);
        
        $this->app->setResponseStatus(201);
    }
}

?>
