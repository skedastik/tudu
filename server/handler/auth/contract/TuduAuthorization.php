<?php
namespace Tudu\Handler\Auth\Contract;

use \Tudu\Core\Data\DbConnection;
use \Tudu\Core\Handler\Auth\Contract\Authorization;

/**
 * Tudu user authorization.
 */
final class TuduAuthorization implements Authorization {
    
    private $db;
    private $userId;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Data\DbConnection $db Database connection instance.
     * @param int $userId User ID.
     */
    public function __construct(DbConnection $db, $userId) {
        $this->db = $db;
        $this->userId = $userId;
    }
    
    public function authorize($param) {
        // TODO
        return false;
    }
}
    
?>
