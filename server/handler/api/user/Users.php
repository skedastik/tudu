<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core;
use \Tudu\Data\Repository;
use \Tudu\Data\Model;
use \Tudu\Core\Error;

/**
 * Request handler for /users/
 */
final class Users extends UserEndpoint {
    
    protected function getAllowedMethods() {
        return 'POST';
    }
    
    /**
     * POST to "/users/" to sign up a new user.
     */
    protected function post() {
        $data = $this->translateRequestBody([
            'email',
            'password'
        ]);
        
        $result = $this->userRepo->signupUser(
            $data['email'],
            $data['password'],
            $this->delegate->getRequestIp()
        );
        if ($result instanceof Error) {
            $this->sendError($result);
        }
        
        $this->delegate->setResponseStatus(201);
        $this->delegate->setResponseHeaders([
            'Location' => '/users/'.$result
        ]);
        $this->renderBody(new Model\User([
            'user_id' => $result
        ]));
    }
}

?>
