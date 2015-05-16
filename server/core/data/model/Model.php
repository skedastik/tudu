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
    private $isSanitized;
    
    /**
     * TODO: Right now, the Model constructor accepts any old array passed in.
     * Maybe each Model subclass should have a static $descriptor array which
     * lists required keys.
     */
    
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
        $this->isSanitized = false;
    }
    
    /**
     * Return properties as a key/value array.
     */
    final public function asArray() {
        return $this->properties;
    }
    
    /**
     * Returns TRUE if model is normalized, FALSE otherwise.
     */
    final public function isNormalized() {
        return $this->isNormalized;
    }
    
    /**
     * Returns TRUE if model is sanitized, FALSE otherwise.
     */
    final public function isSanitized() {
        return $this->isSanitized;
    }
    
    /**
     * This function should return a key/value array of transformers and
     * validators for normalizing data.
     * 
     * Each key is a model property. Each value is an appropriate chain of
     * Validate and possibly Transform objects.
     * 
     * Example:
     * 
     *    [
     *        'user_id'  => Validate::Number()->isPositive(),
     *        
     *        'email'    => Validate::String()->is()->validEmail()->
     *                   -> with()->length()->from(5),
     *                   
     *        'status'   => Transform::String()->capitalize()
     *                   -> then(Validate::String()->length()->upTo(10)),
     *                   
     *        'isActive' => Transform::Convert()->toBooleanString()
     *    ];
     * 
     * Data should never be persisted without being normalized first.
     * Normalization involves validation and possibly transformation. All of
     * this is a separate concern from sanitization. See Model::getSanitizers().
     * 
     * This method must be idempotent.
     * 
     * @return array Key/value array of normalizers.
     */
    abstract protected function getNormalizers();
    
    /**
     * This function should return a key/value array of transformers for
     * sanitizing data prior to output/display.
     * 
     * Each key should be a model property. Each value should be an appropriate
     * Transform chain. When designing the sanitization transforms, you can
     * assume that properties will be normalized before being sanitized.
     * 
     * This method must be idempotent.
     * 
     * @return array Key/value array of sanitizers.
     */
    abstract protected function getSanitizers();
    
    /**
     * Attempt to normalize the model.
     * 
     * Normalization consists of both validation and possibly transformation.
     * 
     * @return array|NULL Key/value array of errors where each key is a property
     * and each value is an error. If there were no errors, NULL is returned.
     * Properties that validate will have transformations applied. Properties
     * that do not validate will not have transformations applied.
     */
    final public function normalize() {
        /**
         * TODO: It might be worthwhile to track normalizations per-property as
         * opposed to per-Model in order to avoid repeated normalizations.
         * Something to consider down the road.
         */
        $normalizers = $this->getCachedNormalizers();
        $errors = $this->applyPropertyFunctors($normalizers);
        $this->isNormalized = is_null($errors);
        return $errors;
    }
    
    /**
     * Sanitize the model.
     * 
     * An error will be thrown if an attempt is made to sanitize a Model object
     * that has not been normalized first.
     */
    final private function sanitize() {
        if (!$this->isNormalized) {
            throw new TuduException('Attempt to sanitize Model object that has not been normalized first.');
        }
        $sanitizers = $this->getCachedSanitizers();
        $this->applyPropertyFunctors($sanitizers);
        $this->isSanitized = true;
    }
    
    /**
     * Get a sanitized copy of this Model object.
     * 
     * The original Model object is not mutated in any way.
     * 
     * Note that this method uses PHP's default __clone() method which performs
     * a shallow copy. Beware of this behavior when extending Model. Per the
     * PHP5 documentation: "any properties that are references to other
     * variables, will remain references".
     * 
     * @return \Tudu\Core\Data\Model\Model A sanitized copy of the model.
     */
    final public function getSanitizedCopy() {
        $model = clone $this;
        $model->sanitize();
        return $model;
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
        $this->isSanitized = false;
    }
    
    /**
     * Apply an array of Validate and/or Transform functors to Model properties.
     * 
     * @param array $functors Key/value array of validators and transformers.
     * @return array|NULL Key/value array of errors where each key is a property
     * and each value is an error. If there were no errors, NULL is returned.
     * Properties that validate will have transformations applied. Properties
     * that do not validate will not have transformations applied.
     */
    final private function applyPropertyFunctors($functors) {
        $errors = [];
        
        foreach ($functors as $property => $functor) {
            $result = $functor->execute($this->properties[$property]);
            if ($result instanceof Sentinel) {
                // error encountered, extract it from the sentinel
                $errors[$property] = $result->getValue();
            } else {
                // property successfully normalized, so apply transform (if any)
                $this->properties[$property] = $result;
            }
        }
        
        return count($errors) === 0 ? null : $errors;
    }
    
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
     * Because getSanitizers is idempotent, it can be called once and
     * cached forever.
     */
    final private function getCachedSanitizers() {
        static $cachedSanitizers = null;
        if (is_null($cachedSanitizers)) {
            $cachedSanitizers = $this->getSanitizers();
        }
        return $cachedSanitizers;
    }
}
?>
