<?php
namespace Tudu\Handler\Api\Task;

/**
 * Request handler for /users/:user_id/tasks/:task_id
 */
final class Task extends \Tudu\Core\Handler\API {
    
    protected function _getAllowedMethods() {
        return 'PUT, DELETE';
    }
    
    protected function put() {
        echo 'Tasks->put()';
    }
    
    protected function delete() {
        echo 'Tasks->delete()';
    }
}

?>
