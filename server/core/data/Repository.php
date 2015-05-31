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
    protected $stagedModel;
 
    public function __construct(DbConnection $db) {
        $this->db = $db;
    }
    
    /**
     * Attempt to normalize a model object, throwing a Validation exception if
     * model is invalid.
     * 
     * Use this method to ensure that all untrusted data passed to a repository
     * method is normalized. Subclass methods should never accept raw arguments
     * from an untrusted source.
     * 
     * @param \Tudu\Core\Data\Model $model
     */
    protected function normalize(Model $model) {
        $errors = $model->normalize();
        if (!is_null($errors)) {
            throw new Exception\Validation(null, $errors, 400);
        }
        return $model;
    }
    
    /**
     * Fetch a single model with matching ID.
     * 
     * @param \Tudu\Core\Data\Model $model Model to match against.
     * @return \Tudu\Core\Data\Model A normalized model populated with data.
     */
    abstract public function getByID(Model $model);
}
?>
