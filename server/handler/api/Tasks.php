<?php
namespace Tudu\Handler\Api;

require_once __DIR__.'/../../core/handler/API.php';

/**
 * Request handler for GET /users/:user_id/tasks/
 */
final class Tasks extends \Tudu\Core\Handler\API {
    
    protected function getAllowedMethods() {
        return 'GET, POST, PUT, DELETE';
    }
    
    protected function get() {
        echo 'Tasks->get()';
    }
    
    protected function post() {
        echo 'Tasks->post()';
    }
    
    protected function put() {
        echo 'Tasks->put()';
    }
    
    protected function delete() {
        echo 'Tasks->delete()';
    }
}

?>