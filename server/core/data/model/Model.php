<?php
namespace Tudu\Core\Data\Model;

use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Core\TuduException;

/**
 * Model base class.
 */
abstract class Model {
    private $properties;
    private $isNormalized;
    
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
        $this->isNormalized = false;
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
    final public function isNormalized() {
        return $this->isNormalized;
    }
    
    /**
     * Return a key/value array of transformers and validators. Each key should
     * be a model property. Each value should be an appropriate
     * Validate/Transform chain.
     * 
     * Example:
     * 
     *    return [
     *        'user_id' => Validate::Number()->isPositive(),
     *        'email'   => Validate::Email()
     *                  -> then(Validate::String()->length()->from(5)),
     *        'status'  => Transform::ToAllCaps()
     *                  -> then(Validate::String()->length()->upTo(10)),
     *        'unicorn' => Validate::Creature()->has()->horns(1)
     *    ];
     * 
     * This method MUST be idempotent.
     * 
     * @return array Key/value array of transformers and validators
     */
    abstract protected function getNormalizers();
    
    /**
     * Because getNormalizers is idempotent, it can be called once and
     * cached forever.
     */
    final private function getCachedNormalizers() {
        static $cachedNormalizers = null;
        if (is_null($cachedNormalizers)) {
            $cachedNormalizers = $this->getNormalizers();
        }
        return $cachedNormalizers;
    }
    
    /**
     * Attempt to normalize the model.
     * 
     * Normalization consists of both transformation and validation.
     * 
     * @return array|NULL Key/value array of errors where each key is a property
     * and each value is either an error string or NULL if all properties
     * validate. Properties that validate will have transformations applied.
     * Properties that do not validate will not have transformations applied.
     */
    final public function normalize() {
        $normalizers = $this->getCachedNormalizers();
        $errors = [];
        $this->isNormalized = true;
        
        /**
         * TODO: It might be worthwhile to track normalizations per-property as
         * opposed to per-Model in order to avoid repeated normalizations.
         * Something to consider down the road.
         */
        
        foreach ($normalizers as $property => $normalizer) {
            $result = $normalizer->execute($this->properties[$property]);
            if ($result instanceof Sentinel) {
                // error encountered, extract it from the sentinel
                $errors[$property] = $result->getValue();
            } else {
                // property successfully normalized, so apply transform
                $this->properties[$property] = $result;
            }
            $this->isNormalized = $this->isNormalized && !isset($errors[$property]);
        }
        
        return $this->isNormalized ? null : $errors;
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
        $this->isNormalized = false;
    }
}
?>
