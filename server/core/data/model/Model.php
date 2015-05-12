<?php
namespace Tudu\Core\Data\Model;

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
     * Return a key/value array of transformers and validators. Each key should
     * be a model property. Each value should be an appropriate
     * Validate/Transform chain.
     * 
     * Example:
     * 
     *    return [
     *        'user_id' => (new Validate\ID())->isPositive()->isNotNull(),
     *        'email'   => (new Validate\Email())
     *                     ->then((new Validate\String())->length()->from(5)),
     *        'status'  => (new Transform\ToAllCaps())
     *                     ->then((new Validate\String())->length()->upTo(10)),
     *        'unicorn' => (new Validate\Creature())->has()->horns(1)
     *    ];
     * 
     * This method MUST be idempotent.
     * 
     * @return array Key/value array describing normalization matrix
     */
    abstract protected function getNormalizationMatrix();
    
    /**
     * Because getNormalizationMatrix is idempotent, it can be called once and
     * cached forever.
     */
    final private function getCachedNormalizationMatrix() {
        static $cachedMatrix = null;
        if (is_null($cachedMatrix)) {
            $cachedMatrix = $this->getNormalizationMatrix();
        }
        return $cachedMatrix;
    }
    
    /**
     * Validate the model.
     * 
     * @return array|NULL Key/value array of errors where each key is a property
     * and each value is either an error string or NULL if the property
     * validates. If all properties are valid, NULL is returned.
     */
    final public function validate() {
        $matrix = $this->getCachedNormalizationMatrix();
        $errors = [];
        $this->isValid = true;
        
        foreach ($matrix as $property => $validator) {
            $errors[$property] = $validator->execute($this->properties[$property]);
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
