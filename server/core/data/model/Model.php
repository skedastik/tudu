<?php
namespace Tudu\Core\Data\Model;

require_once __DIR__.'/../../TuduException.php';

use \Tudu\Core\TuduException;

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
    final public function __construct($properties) {
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
     * Return a key/value array of validators. Each key should be a model
     * property. Each value should be an appropriate validator.
     * 
     * Example:
     * 
     *    return [
     *        'user_id' => Validate\ID()->isPositive()->isNotNull(),
     *        'email'   => Validate\Email()
     *                     ->also(Validate\String()->length()->from(5)),
     *        'unicorn' => Validate\Creature()->has()->horn(1)
     *    ];
     * 
     * @return array Key/value array describing validation matrix
     */
    abstract protected function getValidationMatrix();
    
    /**
     * Validate the model.
     * 
     * @return array|NULL Key/value array of errors where each key is a property
     * and each value is either an error string or NULL if the property
     * validates. If all properties are valid, NULL is returned.
     */
    final public function validate() {
        $matrix = $this->getValidationMatrix();
        $errors = [];
        $this->isValid = true;
        
        foreach ($matrix as $property => $validator) {
            $errors[$property] = $validator->validate($this->properties[$property]);
            $this->isValid = $this->isValid && is_null($errors[$property]);
        }
        
        return $this->isValid ? null : $errors;
    }
    
    /**
     * Get value of property.
     * 
     * @param string $property The property.
     */
    final public function get($property) {
        return $this->properties[$property];
    }
    
    /**
     * Set value of property. Invalidates the model.
     * 
     * @param string $property The property.
     * @param mixed $value The value.
     */
    final public function set($property, $value) {
        $this->properties[$property] = $value;
        $this->isValid = false;
    }
}
?>
