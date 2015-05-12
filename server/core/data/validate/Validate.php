<?php
namespace Tudu\Core\Data\Validate;

/**
 * Chainable data validation base class.
 * 
 * EXTENDING
 * 
 * When extending Validate, you must override process() to carry out the
 * actual validation.
 * 
 * In process(), return an error string if validation fails. If validation
 * succeeds, you must pass data to the next object in the chain via
 * `$this->pass($data)`.
 * 
 * The error string returned should follow these example formats:
 * 
 *    "must be longer than 10 characters."
 *    "should be shorter than two dwarves."
 *    "cannot be a unicorn."
 *    "is too frobnicated."
 *    ...
 * 
 * Notice the lack of capitalization, as the description will be provided
 * automatically. Remember: The error string may be presented to the end
 * user, so make it as presentable as possible while still being precise.
 */
abstract class Validate extends \Tudu\Core\Chainable {
    
    protected $description;
    
    /**
     * Shorthand factory functions for subclasses.
     */
    public static function Email() { return new Email(); }
    public static function String() { return new String(); }
    
    /**
     * Constructor. You must call this from subclass constructors.
     */
    public function __construct() {
        parent::__construct();
        $this->description = "This";
    }
    
    /**
     * Validate data.
     * 
     * @param mixed $data The data to validate.
     * @return string|NULL NULL if data validates, error string otherwise.
     */
    final public function execute($data) {
        if ($data instanceof \Tudu\Core\Data\Validate\Sentinel\Sentinel) {
            return $data->getError();
        }
        $result = $this->process($data);
        return is_null($result) ? NULL : $this->description.' '.$result;
    }
    
    /**
     * If processing reaches the end of the chain, then no validation errors
     * occurred, so return NULL.
     */
    protected function finalize($data) {
        return NULL;
    }
    
    /**
     * Provide a custom description for pretty-printing validation error
     * messages. Validate uses "This" by default. Note the capitalization, as
     * the description will appear at the beginning of the error message. If 
     * validators are chained, the description of the first validator will be
     * used.
     * 
     * Examples:
     * 
     *    "Email address"
     *    "Oops! It looks like your email address"
     *    "This email address"
     * 
     * Notice that the final segment of each description is a noun. Abide by
     * this format, always.
     * 
     * @param string $description A description describing the data to be
     * validated.
     */
    final public function describeAs($description) {
        $this->description = $description;
        return $this;
    }
}
?>
