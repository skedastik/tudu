<?php
namespace Tudu\Core\Data\Validate;

/**
 * Data validator base class.
 * 
 * TODO: Document validator chaining w/ example, e.g.:
 * 
 *    $validator = (new Validate\email())
 *    ->also((new Validate\string())->from(5)->upTo(32))
 *    ->also((new Validate\charset())->in('utf-8', 'ascii'));
 *    
 *    $validator = Email()->isCorrectFormat()
 *    ->also(Length()->from(5)->to(32))
 *    ->also(Charset()->in('utf-8', 'ascii'));
 *    
 *    $validator->validate('foo@bar.com');
 */
abstract class Validate {
    protected $next;
    protected $last;
    protected $noun;
    
    /**
     * Constructor. You MUST call this from subclass constructors.
     */
    public function __construct() {
        $this->next = null;
        $this->last = $this;
        $this->noun = "This";
    }
    
    /**
     * Validate data.
     * 
     * @param mixed $data The data to validate.
     * @return string|NULL NULL if data validates, error string otherwise.
     */
    final public function validate($data) {
        $result = $this->_validate($data);
        return is_null($result) ? NULL : $this->noun.' '.$result;
    }
    
    /**
     * Internal validator method. Override this method. If data validation
     * fails, return an appropriate error string. Otherwise, you MUST
     * `return $this->pass()`.
     * 
     * The error string returned should match the following example formats:
     * 
     *    "must be longer than 10 characters."
     *    "should be shorter than two dwarves."
     *    "cannot be a unicorn."
     *    ...
     * 
     * Notice the lack of capitalization as the noun will be provided
     * automatically.
     * 
     * @param mixed $data The data to validate.
     * @return string|NULL NULL if data validates, error string otherwise.
     */
    protected function _validate($data) {
        /* TODO: Handle sentinels */
        return $this->pass();
    }
    
    /**
     * Invoke the next validator in the chain.
     * 
     * @return string|NULL NULL if data validates, error string otherwise.
     */
    final protected function pass() {
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
    }
    
    /**
     * Provide a noun for pretty-printing validation error messages, otherwise
     * "This" will be used. Note the capitalization, as the noun will appear at
     * the beginning of the error message.
     * 
     * @param string $noun A noun describing the data to be validated, e.g.
     * "E-mail address".
     */
    final public function describeAs($noun) {
        $this->noun = $noun;
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

/**
 * Data validator sentinel class. You can subclass Sentinel and pass it to
 * Validate::validate to force an error.
 */
abstract class Sentinel {
    /**
     * Return an error string.
     * 
     * @return string Error string.
     */
    abstract public function getError();
}
?>
