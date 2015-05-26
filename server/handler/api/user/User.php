<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core\Data\Model;

/**
 * Request handler for /users/:user_id
 */
final class User extends UserEndpoint {
    
    protected function _getAllowedMethods() {
        return 'PUT';
    }
    
    protected function put() {
        echo 'Users->put()';
    }
}

?>
