<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Data\Model;

/**
 * Request handler for /users/:user_id
 */
final class User extends \Tudu\Core\Handler\API {
    
    protected function _getAllowedMethods() {
        return 'PUT';
    }
    
    protected function put() {
        $this->negotiateContentType();
        
        $user = new Model\User();
        $data = $this->getNormalizedRequestBody($user, [
            Model\User::EMAIL,
            Model\User::PASSWORD
        ]);
    }
}

?>
