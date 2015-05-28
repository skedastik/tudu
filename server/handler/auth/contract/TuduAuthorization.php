<?php
namespace Tudu\Handler\Auth\Contract;

use \Tudu\Core\Data\DbConnection;
use \Tudu\Core\Handler\Auth\Contract\Authorization;
use \Tudu\Data\Repository;

/**
 * Tudu user authorization.
 */
final class TuduAuthorization implements Authorization {
    
    private $db;
    private $resourceOwnerId;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Data\DbConnection $db Database connection instance.
     * @param int $resourceOwnerId (optional) ID of user who owns the requested
     * resource.
     */
    public function __construct(DbConnection $db, $resourceOwnerId = null) {
        $this->db = $db;
        $this->resourceOwnerId = $resourceOwnerId;
    }
    
    public function authorize($requesterId) {
        // only authorize users with "active" status
        $userRepo = new Repository\User($this->db);
        $user = $userRepo->getById($requesterId);
        if ($user->get('status') != 'active') {
            return false;
        }
        
        // only authorize if requester is also resource owner
        if (!is_null($this->resourceOwnerId) && $requesterId != $this->resourceOwnerId) {
            return false;
        }
        
        return true;
    }
}
    
?>
