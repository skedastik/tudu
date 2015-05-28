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
     * Encode a user ID and password as basic HTTP authentication credentials.
     * 
     * @param int|string $id User ID.
     * @param string $password Plain text password.
     * @return string Base-64-encoded credentials.
     */
    public static function encodeCredentials($id, $password) {
        return base64_encode($id.':'.$password);
    }
    
    /**
     * Decode basic HTTP authentication credentials.
     * 
     * @param string $credentials Base-64 encoded credentials.
     * @return array|false Key/value array with 'user_id' and 'password' keys
     * on success, NULL on failure.
     */
    public static function decodeCredentials($credentials) {
        $decoded = base64_decode($credentials);
        if (preg_match('/^([^:]+):(.+)/', $decoded, $matches) !== 1) {
            return null;
        }
        return [
            'user_id' => $matches[1],
            'password' => $matches[2]
        ];
    }
    
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
        $credentials = self::decodeCredentials($param);
        if (is_null($credentials)) {
            return false;
        }
        
        $userId = $credentials['user_id'];
        $userRepo = new Repository\User($this->db);
        $user = is_numeric($userId) ? $userRepo->getById($userId) : $userRepo->getByEmail($userId);
        if ($user instanceof Error) {
            return false;
        }
        
        if (!$this->passwordDelegate->compare($credentials['password'], $user->get('password_hash'))) {
            return false;
        }
        
        // TODO: Make sure user has "active" status.
        
        return true;
    }
}
    
?>
