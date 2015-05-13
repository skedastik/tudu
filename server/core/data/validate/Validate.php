<?php
namespace Tudu\Core\Data\Validate;

use \Tudu\Core\Chainable\Sentinel;

/**
 * Chainable data validation base class.
 * 
 * This class can be instantiated if you need a validator that only generates
 * validation errors for sentinel values.
 */
class Validate extends \Tudu\Core\Chainable\Chainable {
    
    /**
     * Shorthand factory function for default validator. The default validator
     * only reports Sentinel errors.
     */
    public static function Basic() { return new Validate(); }
    
    /**
     * Shorthand factory function for Validate\Email.
     */
    public static function Email() { return new Email(); }
    
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
}
?>
