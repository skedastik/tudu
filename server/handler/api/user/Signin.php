<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Conf\Conf;
use \Tudu\Core\Error;
use \Tudu\Data\Model;
use \Tudu\Data\Repository;

/**
 * Request handler for /users/:user_id/signin
 */
final class Signin extends \Tudu\Core\Handler\API {
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    protected function post() {
        $this->checkResponseAcceptable();
        
        $user = new Model\User();
        $context = $this->getNormalizedContext([
            'user_id' => $user
        ]);
        
        $userId = $context['user_id'];
        $tokenRepo = new Repository\AccessToken($this->db);
        $tokenString = Model\AccessToken::generateTokenString();
        $tokenId = $tokenRepo->createAccessToken(
            $userId,
            $tokenString,
            'login',
            Conf::ACCESS_TOKEN_TTL,
            false,
            $this->app->getRequestIp()
        );
        if ($tokenId instanceof Error) {
            $logger = Logger::getInstance();
            $errDescription = 'Error creating access token during user sign-in.';
            $logger->error($errDescription, $tokenId);
            throw new \Tudu\Core\TuduException($errDescription);
        }
        
        $this->renderBody([
            'access_token' => $tokenString
        ]);
        
        $this->app->setResponseStatus(204);
    }
}

?>
