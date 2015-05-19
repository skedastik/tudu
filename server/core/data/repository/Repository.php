<?php
namespace Tudu\Core\Data\Repository;

/**
 * Data repository base class.
 * 
 * All repository methods either fetch or create/update data. Fetch methods
 * should either return \Tudu\Core\Data\Model object(s) or a \Tudu|Core\Error
 * object if an error occurred.  Modify methods should return NULL on success,
 * or a \Tudu\Core\Error object if an error occurred.
 */
abstract class Repository {
    protected $db;
    
    public function __construct(\Tudu\Core\Data\DbConnection $db) {
        $this->db = $db;
    }
    
    /**
     * Call this function from fetch methods to automatically normalize Model
     * objects prior to returning them.
     * 
     * @param \Tudu\Core\Data\Model $model Model to validate.
     * @return \Tudu\Core\Data\Model|\Tudu\Core\Error If no validation errors
     * occurred, a normalized model object is returned. Otherwise an error
     * object is returned.
     */
    final protected function prenormalize($model) {
        $errors = $model->normalize();
        if (is_null($errors)) {
            return $model;
        }
        return Error::Validation($errors);
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
