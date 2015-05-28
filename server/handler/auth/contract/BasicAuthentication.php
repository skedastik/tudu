<?php
namespace Tudu\Handler\Auth\Contract;

use \Tudu\Core\Handler\Auth\Contract\Authentication;
use \Tudu\Core\Data\DbConnection;
use \Tudu\Data\Repository;
use \Tudu\Core\Error;
use \Tudu\Delegate\PHPass;

/**
 * Basic authentication for Tudu.
 */
final class BasicAuthentication implements Authentication {
    
    private $db;
    
    public function __construct(DbConnection $db) {
        $this->db = $db;
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
        $phpass = new PHPass();
        if (!$phpass->compare($password, $user->get('password_hash'))) {
            return false;
        }
        
        // TODO: Make sure user has "active" status.
        
        return true;
    }
}
    
?>
