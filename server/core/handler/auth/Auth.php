<?php
namespace Tudu\Core\Handler\Auth;

use \Tudu\Conf\Conf;
use \Tudu\Core\Exception;
use \Tudu\Core\Delegate;
use \Tudu\Core\Database\DbConnection;
use \Tudu\Core\Handler\Auth\Contract\Authentication;
use \Tudu\Core\Handler\Auth\Contract\Authorization;

/**
 * Request handler with authentication and authorization.
 */
class Auth extends \Tudu\Core\Handler\Handler {
    
    const AUTHENTICATED_USER_MODEL = 'Auth::AUTHENTICATED_USER_MODEL';
    
    private $authentication;
    private $authorization;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Delegate\App $app Instance of an app delegate.
     * @param \Tudu\Core\Database\DbConnection $db Database connection instance.
     * @param \Tudu\Core\Handler\Auth\Contract\Authentication $authentication An
     * instance of an Authentication implementation. This will be used to
     * authenticate incoming requests.
     * @param \Tudu\Core\Handler\Auth\Contract\Authentication $authorization
     * (optional) An instance of an Authorization implementation. This will be
     * used to authorize incoming requests.
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
    
    protected function handleClientException(Exception\Client $exception) {
        parent::handleClientException($exception);
        if ($exception instanceof Exception\Auth) {
            $this->app->setResponseHeaders([
                'WWW-Authenticate' => $this->authentication->getScheme().' realm="'.Conf::AUTHENTICATION_REALM.'"'
            ]);
        }
    }
    
    final protected function process() {
        $credentials = $this->app->getRequestHeader('Authorization');
        if (is_null($credentials)) {
            throw new Exception\Auth('Authorization header is missing.', null, 401);
        }
        
        // extract scheme and param from authorization credentials
        if (preg_match('/^([^\s]+)\s+(.+)/', $credentials, $matches) !== 1) {
            throw new Exception\Auth('Authorization header is malformed.', null, 401);
        }
        
        $scheme = $matches[1];
        $requiredScheme = $this->authentication->getScheme();
        if ($scheme != $requiredScheme) {
            throw new Exception\Auth('Authorization scheme must be "'.$requiredScheme.'".', null, 401);
        }
        
        $authParam = $matches[2];
        $user = $this->authentication->authenticate($authParam);
        if (is_null($user)) {
            throw new Exception\Auth('Authentication failed.', null, 401);
        }
        
        if ($this->authorization && !$this->authorization->authorize($user)) {
            throw new Exception\Auth('User is not authorized to access this resource.', null, 403);
        }
        
        /**
         * TODO: Reject non-secure requests, possibly emitting a warning and/or
         * revoking access tokens if user sends unencrypted credentials.
         */
        
        // pass authenticated user as context to next handler
        $this->app->setContext([self::AUTHENTICATED_USER_MODEL => $user]);
        $this->app->pass();
    }
}
    
?>
