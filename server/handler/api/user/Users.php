<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core;
use \Tudu\Conf\Conf;
use \Tudu\Data\Repository;
use \Tudu\Data\Model;
use \Tudu\Core\Error;
use \Tudu\Core\Logger;

/**
 * Request handler for /users/
 */
final class Users extends UserEndpoint {
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    /**
     * POST to "/users/" to sign up a new user.
     */
    protected function post() {
        $this->checkResponseAcceptable();
        $this->checkRequestDecodable();
        
        $data = $this->getNormalizedRequestBody([
            'email',
            'password'
        ]);
        
        $userId = $this->userRepo->signupUser(
            $data['email'],
            $data['password'],
            $this->app->getRequestIp()
        );
        if ($userId instanceof Error) {
            $this->sendError($userId);
        }
        
        /**
         * TODO: Send a confirmation email to user. Move access token creation
         * logic to sign-in request handler and send the access token upon
         * successful sign-in.
         */
        
        // $tokenRepo = new Repository\AccessToken($this->db);
        // $tokenString = Model\AccessToken::generateTokenString();
        // $tokenId = $tokenRepo->createAccessToken(
        //     $userId,
        //     $tokenString,
        //     'login',
        //     Conf::ACCESS_TOKEN_TTL,
        //     false,
        //     $ip
        // );
        // if ($tokenId instanceof Error) {
        //     $logger = Logger::getInstance();
        //     $errDescription = 'Unable to create access token while signing up new user.';
        //     $logger->error($errDescription, $tokenId);
        //     throw new \Tudu\Core\TuduException($errDescription);
        // }
        
        $this->app->setResponseStatus(201);
        $this->app->setResponseHeaders([
            'Location' => '/users/'.$userId
        ]);
        $this->renderBody([
            'user_id' => $userId
        ]);
    }
}

?>
