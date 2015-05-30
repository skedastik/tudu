<?php
namespace Tudu\Core\Data;

use \Tudu\Core\Database\DbConnection;
use \Tudu\Core\Exception;

/**
 * Data repository base class.
 * 
 * All repository methods throw a \Tudu|Core\Exception\Client exception if an
 * error occurs.
 */
abstract class Repository {
    protected $db;
    
    public function __construct(DbConnection $db) {
        $this->db = $db;
    }
    
    /**
     * Call this function from fetch methods to normalize Model objects prior to
     * returning them.
     * 
     * @param \Tudu\Core\Data\Model $model Model to normalize.
     * @return \Tudu\Core\Data\Model A normalized Model object.
     */
    final protected function prenormalize($model) {
        $errors = $model->normalize();
        if (is_null($errors)) {
            return $model;
        }
        throw new Exception\Internal('Model exported from repository has validation errors.');
    }
    
    /**
     * Fetch a single Model with the given ID.
     * 
     * @param int $id Model ID.
     * @return mixed A Model or Error object.
     */
    abstract public function getByID($id);
}
?>
