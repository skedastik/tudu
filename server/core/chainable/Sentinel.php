<?php
namespace Tudu\Core\Chainable;

/**
 * Chainable "sentinel" class.
 * 
 * Sentinel objects are useful for representing semantic-free values.
 * 
 * What does that mean? Well, Chainable objects are essentially functions that
 * take an input and produce an output. These inputs and outputs can have any
 * type. But what if you need to pass in some special value that is only
 * recognizable by the Chainable class itself, and has no meaning in any other
 * context? This is a semantic-free value. A sentinel's only purpose is to
 * represent such a value.
 * 
 * For example, say you have a chainable data validation class called Validate.
 * You want to pass in some value representing an error like "User ID not
 * found!" to the validator. To do this, simply extend the Sentinel class and
 * pass an instance to Validate::execute to force a validation error.
 * 
 * To illustrate:
 * 
 *    // Validate extends Chainable
 *    $validator = new Validate\User();
 *    
 *    // generate a "not found" validation error
 *    $validator->execute(new Sentinel('not found'));
 */
final class Sentinel {
    
    protected $value;
    
    /**
     * Constructor.
     * 
     * @param mixed $value (optional) Sentinel value. Defaults to NULL.
     */
    public function __construct($value = null) {
        $this->value = $value;
    }
    
    /**
     * Return the value of the sentinel.
     * 
     * @return mixed Output value.
     */
    public function getValue() {
        return $this->value;
    }
}
?>
