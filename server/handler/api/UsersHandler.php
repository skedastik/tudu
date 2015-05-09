<?php
namespace Tudu\Handler\Api;

require_once __DIR__.'/APIHandler.php';

/**
 * Request handler for GET /users/:user_id/tasks/
 */
final class Users extends APIHandler {
    
    protected function getAllowedMethods() {
        return 'POST, PUT';
    }
    
    protected function post() {
        echo 'Users->post()';
    }
    
    protected function put() {
        echo 'Users->put()';
    }
}

?>
