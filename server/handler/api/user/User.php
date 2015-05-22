<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core\Data\Model;

/**
 * Request handler for /users/:user_id
 */
final class User extends UserEndpoint {
    
    protected function getAllowedMethods() {
        return 'PUT';
    }
    
    protected function put(Model\Model $user) {
        echo 'Users->put()';
    }
}

?>
