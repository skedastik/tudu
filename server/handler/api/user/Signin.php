<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Conf\Conf;
use \Tudu\Core\Exception;
use \Tudu\Data\Model\User;
use \Tudu\Data\Model\AccessToken;
use \Tudu\Data\Repository;
use \Tudu\Core\Handler\Auth\Auth;

/**
 * Request handler for /users/:user_id/signin
 */
final class Signin extends Endpoint {
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    protected function post() {
        $this->negotiateContentType();
        
        $user = $this->app->getContext(Auth::AUTHENTICATED_USER_MODEL);
        $tokenString = AccessToken::generateTokenString();
        $token = new AccessToken([
            AccessToken::USER_ID => $user->get(User::USER_ID),
            AccessToken::TOKEN_STRING => $tokenString
        ]);
        
        $tokenRepo = new Repository\AccessToken($this->db);
        $result = $tokenRepo->createAccessToken(
            $token,
            AccessToken::TYPE_LOGIN,
            Conf::ACCESS_TOKEN_TTL,
            true,
            $this->app->getRequestIp()
        );
        
        $this->renderBody([
            AccessToken::TOKEN_STRING => $tokenString,
            AccessToken::TTL => Conf::ACCESS_TOKEN_TTL
        ]);
        
        $this->app->setResponseStatus(200);
    }
}

?>
