<?php
namespace Tudu\Core\Data;

use \Tudu\Core\Database\DbConnection;
use \Tudu\Core;
use \Tudu\Core\Error;

/**
 * Data repository base class.
 * 
 * All repository methods should return a \Tudu|Core\Error object if an error
 * occurs.
 * 
 * Repository subclasses should not normalize input data. This is the
 * responsibility of the input provider. Output data, however, should be
 * normalized.
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
        throw new Core\Exception('Model exported from repository has validation errors.');
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
