<?php
namespace Tudu\Handler\Api;

require_once __DIR__.'/../../core/handler/API.php';

/**
 * Request handler for GET /users/:user_id/tasks/
 */
final class Users extends \Tudu\Core\Handler\API {
    
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
