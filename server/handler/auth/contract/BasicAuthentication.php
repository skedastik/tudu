<?php
namespace Tudu\Handler\Auth\Contract;

use \Tudu\Core\Handler\Auth\Contract\Authentication;
use \Tudu\Core\Data\DbConnection;
use \Tudu\Data\Repository;
use \Tudu\Data\Model;
use \Tudu\Core\Error;
use \Tudu\Core\Delegate;

/**
 * Basic authentication for Tudu.
 */
final class BasicAuthentication implements Authentication {
    
    private $db;
    private $passwordDelegate;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Data\DbConnection $db Database connection instance.
     * @param \Tudu\Core\Delegate\Password $passwordDelegate Password delegate.
     * This will be used to compare user passwords.
     */
    public function __construct(DbConnection $db, Delegate\Password $passwordDelegate) {
        $this->db = $db;
        $this->passwordDelegate = $passwordDelegate;
    }
    
    public function getScheme() {
        return 'Basic';
    }
    
    public function authenticate($param) {
        // extract user and password from base-64-encoded auth parameter
        $decoded = base64_decode($param);
        if (preg_match('/^([^:]+):(.+)/', $decoded, $matches) !== 1) {
            return false;
        }
        
        $userId = $matches[1];
        $userRepo = new Repository\User($this->db);
        $user = is_numeric($userId) ? $userRepo->getById($userId) : $userRepo->getByEmail($userId);
        if ($user instanceof Error) {
            return false;
        }
        
        $password = $matches[2];
        if (!$this->passwordDelegate->compare($password, $user->get('password_hash'))) {
            return false;
        }
        
        // TODO: Make sure user has "active" status.
        
        return true;
    }
}
    
?>
