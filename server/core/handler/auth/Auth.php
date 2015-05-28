<?php
namespace Tudu\Core\Handler\Auth;

use \Tudu\Conf\Conf;
use \Tudu\Core\Delegate;
use \Tudu\Core\Data\DbConnection;
use \Tudu\Core\Handler\Auth\Contract\Authentication;
use \Tudu\Core\Handler\Auth\Contract\Authorization;

/**
 * Request handler with authentication and authorization.
 */
class Auth extends \Tudu\Core\Handler\Handler {
    
    private $authentication;
    private $authorization;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Handler\Auth\Contract\Authentication $authentication An
     * instance of an Authentication implementation. This will be used to
     * authenticate incoming requests.
     * @param \Tudu\Core\Handler\Auth\Contract\Authentication $authorization
     * (optional) An instance of an Authorization implementation. This will be
     * used to authorize incoming requests.
     * @param \Tudu\Core\Delegate\App $app Instance of an app delegate.
     * @param \Tudu\Core\Data\DbConnection $db Database connection instance.
     */
    public function __construct(
        Delegate\App $app,
        DbConnection $db,
        Authentication $authentication,
        Authorization $authorization = null
    ) {
        parent::__construct($app, $db);
        $this->authentication = $authentication;
        $this->authorization = $authorization;
    }
    
    /**
     * Halt processing immediately and send appropriate response headers for
     * auth failure.
     * 
     * @param int $status Response status code.
     */
    private function sendAuthError($status) {
        $this->app->setResponseHeaders([
            'WWW-Authenticate' => $this->authentication->getScheme().' realm="'.Conf::AUTHENTICATION_REALM.'"'
        ]);
        $this->app->setResponseStatus($status);
        $this->app->send();
    }
    
    final public function process() {
        $credentials = $this->app->getRequestHeader('Authorization');
        if (is_null($credentials)) {
            $this->sendAuthError(401);
        }
        
        // extract scheme and param from authorization credentials
        if (preg_match('/^([^\s]+)\s+(.+)/', $credentials, $matches) !== 1) {
            $this->sendAuthError(401);
        }
        
        $scheme = $matches[1];
        if ($scheme != $this->authentication->getScheme()) {
            $this->sendAuthError(401);
        }
        
        $authParam = $matches[2];
        $userId = $this->authentication->authenticate($authParam);
        if (is_null($userId)) {
            $this->sendAuthError(401);
        }
        
        if ($this->authorization && !$this->authorization->authorize($userId)) {
            $this->sendAuthError(403);
        }
        
        /**
         * TODO: Reject non-secure requests, possibly emitting a warning and/or
         * revoking access tokens if user sends unencrypted credentials.
         */
        
        $this->app->setContext(['user_id' => $userId]);
        $this->app->pass();
    }
}
    
?>
