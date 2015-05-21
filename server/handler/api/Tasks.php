<?php
namespace Tudu\Handler\Api;

/**
 * Request handler for /users/:user_id/tasks/
 */
final class Tasks extends \Tudu\Core\Handler\API {
    
    protected function getAllowedMethods() {
        return 'GET, POST';
    }
    
    protected function get() {
        echo 'Tasks->get()';
    }
    
    protected function post() {
        echo 'Tasks->post()';
    }
}

?>
