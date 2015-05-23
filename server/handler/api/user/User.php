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
    
    protected function getRequiredResourceFormat() {
        return [];
    }
    
    protected function put() {
        echo 'Users->put()';
    }
}

?>
