<?php
namespace Tudu\Handler\Api;

use \Tudu\Data\Repository;
use \Tudu\Data\Model;
use \Tudu\Core\Error;

/**
 * Request handler for /users/
 */
final class Users extends \Tudu\Core\Handler\API {
    
    protected function getAllowedMethods() {
        return 'POST';
    }
    
    /**
     * POST to "/users/" to sign up a new user.
     */
    protected function post() {
        $userRepo = new Repository\User($this->db);
        $data = json_decode($this->delegate->getRequestBody(), true);
        
        /**
         * TODO: Consider: The password must be sent in plain-text. This means
         * we need to add a 'password' validator to the Model, which in turn
         * means we need to normalize the model prior to passing its data to the
         * repo. But the repo already normalizes data--so a double normalize
         * will take place.
         * 
         * Solution: Do not call normalize from the repo. Instead, always
         * normalize prior to passing it data.
         * 
         * The question then becomes: How do you enforce the requirement that
         * all data passed to a repo must be normalized?
         */
        
        $result = $userRepo->signupUser(
            $data['email'],
            $data['password_hash'],
            $this->delegate->getRequestIp()
        );
        
        if ($result instanceof Error) {
            $statusCode = $result->getHttpStatusCode();
            $this->delegate->setResponseStatus(is_null($statusCode) ? 400 : $statusCode);
            $responseBody = json_encode($result->asArray());
        } else {
            /**
             * TODO: Provide link to new resource.
             */
            $this->delegate->setResponseStatus(201);
            $this->delegate->setResponseHeaders([
                'Location' => '/users/'.$result->get('user_id')
            ]);
        }
        
        echo $responseBody;
    }
}

?>
