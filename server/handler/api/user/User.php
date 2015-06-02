<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Data\Repository;
use \Tudu\Data\Model;
use \Tudu\Core\Handler\Auth\Auth;

/**
 * Request handler for /users/:user_id
 */
final class User extends Endpoint {
    
    protected function _getAllowedMethods() {
        return 'PUT';
    }
    
    protected function put() {
        $this->negotiateContentType();
        
        $user = $this->importRequestData([
            Model\User::USER_ID
        ]);
        $userRepo = new Repository\User($this->db);
        $userRepo->updateUser($user, $this->app->getRequestIp());
        
        $this->app->setResponseStatus(204);
    }
}

?>
