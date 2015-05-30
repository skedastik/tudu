<?php
namespace Tudu\Handler\Auth\Contract;

use \Tudu\Core\Handler\Auth\Contract\Authorization;
use \Tudu\Core\Data\Model;
use \Tudu\Data\Model\User;

/**
 * Tudu user authorization.
 */
final class TuduAuthorization implements Authorization {
    
    private $resourceOwnerId;
    
    /**
     * Constructor.
     * 
     * @param int $resourceOwnerId (optional) ID of user who owns the requested
     * resource.
     */
    public function __construct($resourceOwnerId = null) {
        $this->resourceOwnerId = $resourceOwnerId;
    }
    
    public function authorize(Model $requester) {
        // only authorize users with "active" status
        if ($requester->get('status') != 'active') {
            return false;
        }
        
        // only authorize if requester is also resource owner
        if (!is_null($this->resourceOwnerId) && $requester->get(User::USER_ID) != $this->resourceOwnerId) {
            return false;
        }
        
        return true;
    }
}
    
?>
