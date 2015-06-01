<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Data\Repository;
use \Tudu\Data\Model;
use \Tudu\Core\Handler\Auth\Auth;

/**
 * Request handler for /users/:user_id
 */
final class User extends Endpoint {
    
    protected function _getAllowedMethods() {
        return 'PUT';
    }
    
    protected function put() {
        $this->negotiateContentType();
        
        $user = $this->importRequestData();
        $newEmail = $user->get(Model\User::EMAIL);
        $newPassword = $user->get(Model\User::PASSWORD);
        
        $user = $this->getContext(Auth::AUTHENTICATED_USER_MODEL);
        $userRepo = new Repository\User($this->db);
        
        // update email address if it has changed
        if ($newEmail != $user->get('email')) {
            $userRepo->setUserEmail(
                $user->get('user_id'),
                $newEmail,
                $this->app->getRequestIp()
            );
        }
        
        if (isset($data[Model\User::NEW_PASSWORD])) {
            $newPassword = $data[Model\User::NEW_PASSWORD];
        }
    }
}

?>
