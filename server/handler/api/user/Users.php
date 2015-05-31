<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core\Database\DbConnection;
use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;
use \Tudu\Core\Exception;

/**
 * Request handler for /users/
 */
final class Users extends \Tudu\Core\Handler\API {
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    /**
     * POST to "/users/" to sign up a new user.
     */
    protected function post() {
        $this->negotiateContentType();
        
        $user = $this->importRequestData(new User(), [
            User::EMAIL,
            User::PASSWORD
        ]);
        $userRepo = new Repository\User($this->db);
        $userId = $userRepo->signupUser($user, $this->app->getRequestIp());
        
        /**
         * TODO: Send a confirmation email to user.
         */
        
        $this->app->setResponseStatus(201);
        $this->app->setResponseHeaders([
            // TODO: Do not hard-code route here
            'Location' => '/users/'.$userId
        ]);
        $this->renderBody([
            User::USER_ID => $userId
        ]);
    }
}

?>
