<?php
namespace Tudu\Handler\Auth\Contract;

use \Tudu\Core\Delegate;
use \Tudu\Core\Handler\Auth\Contract\Authentication;

/**
 * HMAC-inspired Tudu user authentication.
 */
final class TuduAuthentication implements Authentication {
    
    protected $app;
    private $db;
    private $userId;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Delegate\App $app Instance of an app delegate.
     * @param \Tudu\Core\Data\DbConnection $db Database connection instance.
     * @param int $userId ID of user making the request.
     */
    public function __construct(Delegate\App $app, DbConnection $db, $userId) {
        $this->app = $app;
        $this->db = $db;
        $this->userId = $userId;
    }
    
    public function getScheme() {
        return 'Tudu';
    }
    
    public function authenticate($param) {
        /**
         * TODO: Reject non-secure requests and immediately revoke access tokens
         * that are sent over unencrypted connections.
         * TODO: Perform HMAC-inspired authentication.
         */
        return false;
    }
}
    
?>
