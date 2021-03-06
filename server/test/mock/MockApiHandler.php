<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Handler;
use \Tudu\Test\Mock\MockModel;
use \Tudu\Test\Mock\MockRepository;

/**
 * Mock API request handler
 */
final class MockApiHandler extends Handler\API {
    
    protected $context;
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    protected function getModel() {
        return new MockModel();
    }
    
    protected function post() {
        $this->negotiateContentType();
        $model = $this->importRequestData([
            'name',
            'email'
        ]);
        $mockRepo = new MockRepository($this->db);
        $mockRepo->fetch($model);
        $this->app->setResponseStatus(201);
    }
    
    protected function put() {
        $model = $this->importRequestData([
            'name'
        ]);
        $mockRepo = new MockRepository($this->db);
        $mockRepo->fetch($model);
        echo $model->get('name');
    }
    
    public function publicRenderBody($data) {
        return parent::renderBody($data);
    }
}

?>
