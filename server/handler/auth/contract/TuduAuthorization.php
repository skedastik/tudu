<?php
namespace Tudu\Handler\Auth\Contract;

use \Tudu\Core\Data\DbConnection;
use \Tudu\Core\Handler\Auth\Contract\Authorization;

/**
 * Tudu user authorization.
 */
final class TuduAuthorization implements Authorization {
    
    private $userId;
    private $db;
    
    public function __construct($userId, DbConnection $db) {
        $this->userId = $userId;
        $this->db = $db;
    }
    
    public function authorize($param) {
        // TODO
        return false;
    }
}
    
?>
