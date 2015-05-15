<?php
namespace Tudu\Core\Chainable;

/**
 * Sentinel objects are useful for representing semantic-free values.
 * 
 * What does that mean? Well, Chainable objects are essentially functions that
 * take an input and produce an output. These inputs and outputs can have any
 * type. But what if you need to pass in some special value that is only
 * recognizable by the Chainable class itself, and has no meaning in any other
 * context? This is a semantic-free value. A sentinel's only purpose is to
 * represent such a value.
 * 
 * When extending Chainable, you have the option to perform special processing
 * for Sentinel inputs by overriding Chainable::processSentinel.
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
