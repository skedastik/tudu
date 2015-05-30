<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Conf\Conf;
use \Tudu\Core\Error;
use \Tudu\Data\Model\AccessToken;
use \Tudu\Data\Repository;
use \Tudu\Core\Handler\Auth\Auth;

/**
 * Request handler for /users/:user_id/signin
 */
final class Signin extends \Tudu\Core\Handler\API {
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    protected function post() {
        $this->negotiateContentType();
        
        $user = $this->app->getContext()[Auth::AUTHENTICATED_USER_MODEL];
        $tokenRepo = new Repository\AccessToken($this->db);
        $tokenString = AccessToken::generateTokenString();
        $result = $tokenRepo->createAccessToken(
            $user->get('user_id'),
            $tokenString,
            'login',
            Conf::ACCESS_TOKEN_TTL,
            true,
            $this->app->getRequestIp()
        );
        if ($result instanceof Error) {
            $logger = Logger::getInstance();
            $errDescription = 'Error creating access token during user sign-in.';
            $logger->error($errDescription, $result->asArray());
            throw new \Tudu\Core\TuduException($errDescription);
        }
        
        $this->renderBody([
            AccessToken::TOKEN_STRING => $tokenString,
            AccessToken::TTL => Conf::ACCESS_TOKEN_TTL
        ]);
        
        $this->app->setResponseStatus(200);
    }
}

?>
