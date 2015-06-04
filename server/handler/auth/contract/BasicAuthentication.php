<?php
namespace Tudu\Handler\Auth\Contract;

use \Tudu\Core\Handler\Auth\Contract\Authentication;
use \Tudu\Core\Database\DbConnection;
use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;
use \Tudu\Core\Exception;

/**
 * Basic authentication for Tudu.
 */
final class BasicAuthentication implements Authentication {
    
    private $db;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Database\DbConnection $db Database connection instance.
     */
    public function __construct(DbConnection $db) {
        $this->db = $db;
    }
    
    public function getScheme() {
        return 'Basic';
    }
    
    public function authenticate($param) {
        // extract user and password from base-64-encoded auth parameter
        $credentials = self::decodeCredentials($param);
        
        if (is_null($credentials)) {
            return null;
        }
        
        $id = $credentials['id'];
        $userRepo = new Repository\User($this->db);
        
        $property = is_numeric($id) ? User::USER_ID : User::EMAIL;
        try {
            $user = $userRepo->fetch(new User([
                $property => $id
            ]));
        } catch (Exception\Client $e) {
            return null;
        }
        
        if (!User::getPasswordDelegate()->compare($credentials['password'], $user->get(User::PASSWORD_HASH))) {
            return null;
        }
        
        return $user;
    }
    
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
     * @return array|false Key/value array with 'id' and 'password' keys on
     * success, NULL on failure.
     */
    private static function decodeCredentials($credentials) {
        $decoded = base64_decode($credentials);
        if (preg_match('/^([^:]+):(.+)/', $decoded, $matches) !== 1) {
            return null;
        }
        return [
            'id' => $matches[1],
            'password' => $matches[2]
        ];
    }
}
    
?>
