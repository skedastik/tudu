<?php
namespace Tudu\Core\Data\Repository;

require_once __DIR__.'/../DbConnection.php';
require_once __DIR__.'/../model/Model.php';

/**
 * Data repository base class.
 */
abstract class Repository {
    protected $db;
    
    public function __construct(\Tudu\Core\Data\DbConnection $db) {
        $this->db = $db;
    }
    
    /**
     * Get a single Model with the given ID.
     * 
     * @param int $id Model ID.
     * @return \Tudu\Core\Data\Model\Model 
     */
    abstract public function getById($id);
}
?>