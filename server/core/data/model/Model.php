<?php
namespace Tudu\Core\Data\Model;

/**
 * Model base class.
 */
abstract class Model {
    private $properties;
    private $isValid;
    
    /**
     * Constructor.
     * 
     * @param array $properties Key/value properties array.
     */
    final public function __construct($model) {
        $this->fromArray($properties);
    }
    
    /**
     * Set properties using a key/value array. Invalidates the model.
     * 
     * @param array $properties Key/value array of properties.
     */
    final public function fromArray($properties) {
        $this->properties = $properties;
        $this->isValid = false;
    }
    
    /**
     * Return properties as a key/value array.
     */
    final public function asArray() {
        return $this->properties;
    }
    
    /**
     * Returns TRUE if model is valid, FALSE otherwise.
     */
    final public function isValid() {
        return $this->isValid;
    }
    
    /**
     * Validate the model.
     * 
     * @return array|NULL Key/value array of errors where each key is a property
     * and each value is either an error string or NULL if the property
     * validates. If all properties are valid, NULL is returned.
     */
    final public function validate() {
        /* TODO */
    }
}
?>
