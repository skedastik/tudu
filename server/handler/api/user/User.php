<?php
namespace Tudu\Handler\Api\User;

/**
 * Request handler for /users/:user_id
 */
final class User extends \Tudu\Core\Handler\API {
    
    protected function _getAllowedMethods() {
        return 'PUT';
    }
    
    protected function put() {
        echo 'Users->put()';
    }
}

?>
