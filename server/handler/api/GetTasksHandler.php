<?php
namespace Tudu\Handler\Api\Get;

require_once __DIR__.'/APIHandler.php';

/**
 * Request handler for GET /users/:user_id/tasks/
 */
class Tasks extends \Tudu\Handler\Api\APIHandler {
    
    protected function acceptAuthentication() {
        echo 'Hello world!';
    }
}

?>
