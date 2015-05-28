<?php
namespace Tudu\Handler\Auth\Contract;

use \Tudu\Core\Data\DbConnection;
use \Tudu\Core\Handler\Auth\Contract\Authorization;

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
        /**
         * TODO: Ensure requester has "active" status
         * TODO: Ensure resource owner matches requester
         */
        return false;
    }
}
    
?>