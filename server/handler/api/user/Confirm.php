<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core\Error;
use \Tudu\Data\Model;

/**
 * Request handler for /users/:user_id/confirm
 */
final class Confirm extends UserEndpoint {
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    protected function post() {
        $this->checkRequestDecodable();
        
        $context = $this->getNormalizedContext([
            'user_id' => new Model\User()
        ]);
        
        $data = $this->getNormalizedRequestBody([
            'signup_token'
        ]);
        
        $result = $this->userRepo->confirmUser(
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
