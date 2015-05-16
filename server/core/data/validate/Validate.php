<?php
namespace Tudu\Core\Data\Validate;

use \Tudu\Core\Chainable\Sentinel;

/**
 * Chainable data validation base class.
 * 
 * Validators take any input. If the input is valid, the validator simply
 * outputs the input. Otherwise, the validator outputs an error string wrapped
 * in a Sentinel object (see \Tudu\Core\Chainable\Sentinel).
 */
class Validate extends \Tudu\Core\Chainable\OptionsChainable {
    
    /**
     * Shorthand factory function for default validator.
     * 
     * The default validator doesn't perform any validation. It simply outputs
     * whatever was input. This means you can input an arbitrary error string
     * wrapped in a Sentinel object to force a validation error. Unless
     * otherwise specified, classes that extend Validate inherit this behavior.
     */
    public static function Basic() { return new Validate(); }
    
    /**
     * Shorthand factory function for Validate\String.
     */
    public static function String() { return new String(); }
    
    /**
     * Constructor. You must call this from subclass constructors.
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Override this method to carry out the actual validation. If validation
     * fails, return an error string wrapped in a sentinel.
     * 
     * The error string should follow these examples:
     * 
     *    "must be longer than 10 characters"
     *    "should be shorter than two dwarves"
     *    "cannot be a unicorn"
     *    "is too frobnicated"
     *    ...
     * 
     * Notice the lack of capitalization and ending punctuation. This is
     * intended and should be emulated. Also, the first word should always be a
     * verb. Remember: The error string may be presented to the end user, so
     * make it as concise as possible while still being readable.
     * 
     * @param string $error Input error.
     * @return \Tudu\Core\Chainable\Sentinel An error sentinel.
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    protected function process($data) {
        return $data;
    }
    
    /**
     * No-op, fluent function.
     */
    final public function length() {
        return $this;
    }
    
    /**
     * No-op, fluent function.
     */
    final public function with() {
        return $this;
    }
    
    /**
     * No-op, fluent function.
     */
    final public function is() {
        return $this;
    }
}
?>
