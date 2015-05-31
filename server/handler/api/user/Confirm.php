<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core\Exception;
use \Tudu\Data\Model\User;
use \Tudu\Data\Repository;

/**
 * Request handler for /users/:user_id/confirm
 */
final class Confirm extends \Tudu\Core\Handler\API {
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    protected function post() {
        $user = $this->importRequestData(new User(), [
            User::USER_ID,
            User::SIGNUP_TOKEN
        ]);
        $userRepo = new Repository\User($this->db);
        $result = $userRepo->confirmUser($user, $this->app->getRequestIp());        
        $this->app->setResponseStatus(204);
    }
}

?>
