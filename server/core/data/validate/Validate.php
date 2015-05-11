<?php
namespace Tudu\Core\Data\Validate;

/**
 * Data validator base class.
 * 
 * Validators can be chained via Validate::then.
 * 
 * Example:
 *    
 *    $validator = (new Validate\Email())
 *        ->then((new Validate\String())->length()->from(5)->upTo(32));
 *    
 *    $validator->validate('foo@bar.com');      // validates, returns NULL
 * 
 * EXTENDING
 * 
 * When extending Validate, you must override process() to carry out the actual
 * validation.
 * 
 * In process(), return an error string if validation fails. If validation
 * succeeds, you must call `parent::process($data)`.
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
    final public function validate($data) {
        if ($data instanceof \Tudu\Core\Data\Validate\Sentinel\Sentinel) {
            $result = $data->getError();
        } else {
            $result = $this->process($data);
        }
        return is_null($result) ? NULL : $this->description.' '.$result;
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
