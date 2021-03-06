<?php
namespace Tudu\Core\Data;

use \Tudu\Core\Arrayable;
use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Core\Exception;

/**
 * Model base class.
 */
abstract class Model implements Arrayable {
    
    private static $normalizerCache;
    private static $sanitizerCache;
    
    private $properties;
    private $isNormalized;
    private $isSanitized;
    
    /**
     * Constructor.
     * 
     * @param array $properties (optional) Key/value properties array.
     * @param bool $prenormalized (optional) Pass TRUE to mark model as
     * normalized upon instantiation. This should only be done if you are
     * fetching data from a trusted source such as the database. Defaults to
     * FALSE.
     */
    final public function __construct($properties = [], $prenormalized = false) {
        $this->fromArray($properties);
        $this->isNormalized = $prenormalized;
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
        return $this;
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
     * Validate and possibly Transform objects. There is no need to call
     * `done()` on each chain. This is performed automatically.
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
     *        'isActive' => Transform::Convert()->to()->booleanString()
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
     * This function should return a key/value array of sanitization schemes and
     * corresponding transformers for sanitizing data prior to output/display.
     * 
     * The key-value array has two dimensions. The top-level key should be the
     * sanitization scheme, e.g., 'html' or 'email'. Each value will be another
     * key/value array where each key is a model property. Each value therein
     * should be an appropriate Transform chain. When designing the sanitization
     * transforms, you can assume that properties will be normalized before
     * being sanitized.
     * 
     * Example:
     * 
     *    [
     *        'html' => [
     *            'name' => Transform::String()->escapeForHtml(),
     *        ],
     *        
     *        'email' => [
     *            'name' => Transform::String()->escapeForEmail()
     *        ]
     *    ];
     * 
     * This method must be idempotent.
     * 
     * @return array Key/value array of sanitizers.
     */
    abstract protected function getSanitizers();
    
    /**
     * Override this static property to specify a key/value array of model
     * property aliases.
     * 
     * Example:
     * 
     *    [
     *        'first_name' => 'name'
     *        'middle_name' => 'name'
     *        'last_name' => 'name'
     *    ]
     * 
     * By setting the above array, 'first_name', 'middle_name', and 'last_name'
     * will inherit the normalizers and sanitizers from the 'name' property.
     */
    protected static $propertyAliases = [];
    
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
         * TODO: It may be worthwhile to track normalization per-property as
         * well as per-model to avoid repeated normalizations. Something to
         * consider down the road.
         */
        if (!$this->isNormalized) {
            $normalizers = self::getCachedNormalizers();
            $errors = $this->applyPropertyFunctors($normalizers);
            $this->isNormalized = is_null($errors);
            return $errors;
        }
    }
    
    /**
     * Sanitize the model using the given sanitization scheme.
     * 
     * An error will be thrown if an attempt is made to sanitize a Model object
     * that has not been normalized first.
     * 
     * @param string $scheme Sanitization scheme (e.g., 'html', 'email').
     */
    final private function sanitize($scheme) {
        if (!$this->isSanitized) {
            if (!$this->isNormalized) {
                throw new Exception\Internal('Attempt to sanitize Model object that has not been normalized first.');
            }
            $sanitizers = self::getCachedSanitizers();
            if (!isset($sanitizers[$scheme])) {
                throw new Exception\Internal('Attempt to sanitize Model using nonexistent scheme "'.$scheme.'".');
            }
            $this->applyPropertyFunctors($sanitizers[$scheme]);
            $this->isSanitized = true;
        }
    }
    
    /**
     * Get a sanitized copy of this Model object using the given sanitization
     * scheme.
     * 
     * The original Model object is not mutated in any way.
     * 
     * Note that this method uses PHP's default __clone() method which performs
     * a shallow copy. Beware of this behavior when extending Model. Per the
     * PHP5 documentation: "any properties that are references to other
     * variables, will remain references".
     * 
     * @param string $scheme Sanitization scheme (e.g., 'html', 'email').
     * @return \Tudu\Core\Data\Model\Model A sanitized copy of the model.
     */
    final public function getSanitizedCopy($scheme) {
        $model = clone $this;
        $model->sanitize($scheme);
        return $model;
    }
    
    /**
     * Get value of property.
     * 
     * @param string $property The property.
     * @return mixed The value. Returns NULL if property has not been set.
     */
    final public function get($property) {
        return isset($this->properties[$property]) ? $this->properties[$property] : null;
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
     * Check if the model has a given property.
     * 
     * @param string $property A property.
     * @return bool TRUE if model has property, FALSE otherwise.
     */
    final public function hasProperty($property) {
        return array_key_exists($property, $this->properties);
    }
    
    /**
     * Check if the model has given properties.
     * 
     * @param array $properties An array of property keys.
     * @return bool TRUE if model has all of supplied properties, FALSE
     * otherwise.
     */
    final public function hasProperties(array $properties) {
        foreach ($properties as $property) {
            if (!array_key_exists($property, $this->properties)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Apply an array of Validate and/or Transform functors to Model properties.
     * 
     * Properties that validate will have transformations applied. Properties
     * that do not validate will not have transformations applied.
     * 
     * @param array $functors Key/value array of validators and transformers.
     * @return array|NULL Key/value array of errors where each key is a property
     * and each value is an error. If there were no errors, NULL is returned.
     */
    final private function applyPropertyFunctors($functors) {
        $errors = [];
        foreach ($this->properties as $property => $value) {
            if (isset($functors[$property])) {
                $result = $functors[$property]->execute($value);
                if ($result instanceof Sentinel) {
                    // error encountered, extract it from the sentinel
                    $errors[$property] = $result->getValue();
                } else {
                    // property successfully normalized, so apply transform (if any)
                    $this->properties[$property] = $result;
                }
            }
        }
        return count($errors) === 0 ? null : $errors;
    }
    
    /**
     * Because getNormalizers is idempotent, it can be called once and cached
     * forever.
     */
    final private function getCachedNormalizers() {
        $key = get_class($this);
        if (!isset(self::$normalizerCache[$key])) {
            $normalizers = $this->getNormalizers();
            foreach ($normalizers as $property => $normalizer) {
                $normalizers[$property] = $normalizers[$property]->done();
            }
            foreach (static::$propertyAliases as $alias => $property) {
                $normalizers[$alias] = $normalizers[$property];
            }
            self::$normalizerCache[$key] = $normalizers;
        }
        return self::$normalizerCache[$key];
    }
    
    /**
     * Because getSanitizers is idempotent, it can be called once and cached
     * forever.
     */
    final private function getCachedSanitizers() {
        $key = get_class($this);
        if (!isset(self::$sanitizerCache[$key])) {
            $sanitizers = $this->getSanitizers();
            foreach (static::$propertyAliases as $alias => $property) {
                foreach (array_keys($sanitizers) as $scheme) {
                    foreach ($sanitizers[$scheme] as $property => $sanitizer) {
                        $sanitizers[$scheme][$property] = $sanitizers[$scheme][$property]->done();
                    }
                    if (isset($sanitizers[$scheme][$property])) {
                        $sanitizers[$scheme][$alias] = $sanitizers[$scheme][$property];
                    }
                }
            }
            self::$sanitizerCache[$key] = $sanitizers;
        }
        return self::$sanitizerCache[$key];
    }
}
?>
