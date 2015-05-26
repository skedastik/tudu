<?php
namespace Tudu\Handler\Api\Task;

/**
 * Request handler for /users/:user_id/tasks/
 */
final class Tasks extends TaskEndpoint {
    
    protected function _getAllowedMethods() {
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
