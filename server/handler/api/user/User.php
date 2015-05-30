<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Data\Repository;
use \Tudu\Data\Model;
use \Tudu\Core\Handler\Auth\Auth;

/**
 * Request handler for /users/:user_id
 */
final class User extends \Tudu\Core\Handler\API {
    
    protected function _getAllowedMethods() {
        return 'PUT';
    }
    
    protected function put() {
        $this->negotiateContentType();
        
        $userModel = new Model\User();
        $data = $this->getNormalizedRequestBody($userModel, [
            Model\User::EMAIL
        ]);
        
        $user = $this->getContext(Auth::AUTHENTICATED_USER_MODEL);
        $userRepo = new Repository\User($this->db);
        
        $email = $data[Model\User::EMAIL];
        if ($email != $user->get('email')) {
            $userRepo->setUserEmail(
                $user->get('user_id'),
                $email,
                $this->app->getRequestIp()
            );
        }
        
        if (isset($data[Model\User::NEW_PASSWORD])) {
            $newPassword = $data[Model\User::NEW_PASSWORD];
        }
    }
}

?>
