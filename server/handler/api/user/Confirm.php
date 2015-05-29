<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core\Error;
use \Tudu\Data\Model;
use \Tudu\Data\Repository;

/**
 * Request handler for /users/:user_id/confirm
 */
final class Confirm extends \Tudu\Core\Handler\API {
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    protected function post() {
        $user = new Model\User();
        $data = $this->getNormalizedRequestBody($user, [
            'signup_token'
        ]);
        
        $context = $this->getNormalizedContext([
            'user_id' => $user
        ]);
        
        $userRepo = new Repository\User($this->db);
        $result = $userRepo->confirmUser(
            $context['user_id'],
            $data['signup_token'],
            $this->app->getRequestIp()
        );
        if ($result instanceof Error) {
            $this->sendError($result);
        }
        
        $this->app->setResponseStatus(204);
    }
}

?>
