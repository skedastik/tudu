<?php
namespace Tudu\Handler\Api;

/**
 * Request handler for /users/
 */
final class Users extends \Tudu\Core\Handler\API {
    
    protected function getAllowedMethods() {
        return 'POST';
    }
    
    protected function post() {
        echo 'Users->post()';
    }
}

?>
