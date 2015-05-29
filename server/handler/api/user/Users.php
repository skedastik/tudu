<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core\Data\DbConnection;
use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;
use \Tudu\Core\Error;
use \Tudu\Core\Delegate;

/**
 * Request handler for /users/
 */
final class Users extends \Tudu\Core\Handler\API {
    
    private $passwordDelegate;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Delegate\App $app Instance of an app delegate.
     * @param \Tudu\Core\Data\DbConnection $db Database connection instance.
     * @param \Tudu\Core\Delegate\Password $passwordDelegate Password delegate.
     * This will be used to hash user passwords.
     */
    public function __construct(
        Delegate\App $app,
        DbConnection $db,
        Delegate\Password $passwordDelegate
    ) {
        parent::__construct($app, $db);
        $this->passwordDelegate = $passwordDelegate;
    }
    
    protected function _getAllowedMethods() {
        return 'POST';
    }
    
    /**
     * POST to "/users/" to sign up a new user.
     */
    protected function post() {
        $this->negotiateContentType();
        
        $user = new User();
        $data = $this->getNormalizedRequestBody($user, [
            User::EMAIL,
            User::PASSWORD
        ]);
        
        $userRepo = new Repository\User($this->db);
        $userId = $userRepo->signupUser(
            $data[User::EMAIL],
            $this->passwordDelegate->getHash($data[User::PASSWORD]),
            $this->app->getRequestIp()
        );
        if ($userId instanceof Error) {
            $this->sendError($userId);
        }
        
        /**
         * TODO: Send a confirmation email to user.
         */
        
        $this->app->setResponseStatus(201);
        $this->app->setResponseHeaders([
            'Location' => '/users/'.$userId
        ]);
        $this->renderBody([
            User::USER_ID => $userId
        ]);
    }
}

?>
