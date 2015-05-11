<?php
namespace Tudu\Handler\Api;

/**
 * Request handler for GET /users/:user_id/tasks/
 */
final class User extends \Tudu\Core\Handler\API {
    
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
