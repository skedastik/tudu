<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core\Error;

/**
 * Request handler for /users/:user_id/confirm
 */
final class Confirm extends UserEndpoint {
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    protected function post() {
        $this->checkRequestDecodable();
        
        $data = $this->decodeRequestBody([
            'signup_token'
        ]);
        
        $result = $this->userRepo->confirmUser(
            $this->context['user_id'],
            $data['signup_token'],
            $this->app->getRequestIp()
        );
        if ($result instanceof Error) {
            $this->sendError($userId);
        }
        
        $this->app->setResponseStatus(204);
    }
}

?>
