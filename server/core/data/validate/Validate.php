<?php
namespace Tudu\Core\Data\Validate;

require_once __DIR__.'/sentinel/NotFound.php';

/**
 * Data validator base class.
 * 
 * Validators can be chained via Validate::also.
 * 
 * Example:
 * 
 *    // Validate\String() and Validate\CharSet() are shorthand constructors:
 *    
 *    $validator = Validate\Email()
 *        ->also(Validate\String()->length()->from(5)->upTo(32));
 *    
 *    $validator->validate('foo@bar.com');      // validates, returns NULL
 */
abstract class Validate {
    
    protected $next;
    protected $last;
    protected $description;
    
    /**
     * Constructor. You MUST call this from subclass constructors.
     */
    public function __construct() {
        $this->next = null;
        $this->last = $this;
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
            $result = $this->_validate($data);
        }
        return is_null($result) ? NULL : $this->description.' '.$result;
    }
    
    /**
     * Internal validator method. Override this method. If data validation
     * fails, return an appropriate error string. Otherwise, you MUST
     * `return $this->pass($data)`.
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
     * 
     * @param mixed $data The data to validate.
     * @return string|NULL NULL if data validates, error string otherwise.
     */
    protected function _validate($data) {
        return $this->pass($data);
    }
    
    /**
     * Invoke the next validator in the chain.
     * 
     * @param mixed $data The data to validate.
     * @return string|NULL NULL if data validates, error string otherwise.
     */
    final protected function pass($data) {
        if (is_null($this->next)) {
            return NULL;
        }
        return $this->next->_validate($data);
    }
    
    /**
     * Chain another validator.
     * 
     * @param \Tudu\Core\Data\Validate $validator The validator.
     */
    final public function also(Validate $validator) {
        $this->last->setNext($validator);
        $this->last = $validator;
        return $this;
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
    
    /**
     * Set next validator. This is used internally by other Validate objects.
     * Do not call directly.
     * 
     * @param \Tudu\Core\Data\Validate $validator The validator.
     */
    final public function setNext(Validate $validator) {
        $this->next = $validator;
    }
}
?>
