<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core\Error;
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
        $user = new User();
        $data = $this->getNormalizedRequestBody($user, [
            User::SIGNUP_TOKEN
        ]);
        
        $context = $this->getNormalizedContext([
            User::USER_ID => $user
        ]);
        
        $userRepo = new Repository\User($this->db);
        $result = $userRepo->confirmUser(
            $context[User::USER_ID],
            $data[User::SIGNUP_TOKEN],
            $this->app->getRequestIp()
        );
        if ($result instanceof Error) {
            $this->sendError($result);
        }
        
        $this->app->setResponseStatus(204);
    }
}

?>
