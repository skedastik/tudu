<?php
namespace Tudu\Core\Data\Validate;

use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Core\TuduException;

/**
 * String validator.
 * 
 * Use various option methods to set validation options.
 */
final class String extends Validator {
    
    // options
    const OPT_MIN_LENGTH   = 'min_length';
    const OPT_MAX_LENGTH   = 'max_length';
    const OPT_CHECK_LENGTH = 'check_length';
    const OPT_VALIDATE_EMAIL  = 'valid_email';
    
    // Option methods ----------------------------------------------------------
    
    /**
     * Set maximum number of characters allowed.
     */
    public function from($length) {
        $this->addOption(self::OPT_CHECK_LENGTH);
        $this->setOption(self::OPT_MIN_LENGTH, $length);
        return $this;
    }
    
    /**
     * Set minimum number of characters allowed.
     */
    public function upTo($length) {
        $this->addOption(self::OPT_CHECK_LENGTH);
        $this->setOption(self::OPT_MAX_LENGTH, $length);
        return $this;
    }
    
    /**
     * Check that input is a valid email address.
     */
    public function validEmail() {
        $this->addOption(self::OPT_VALIDATE_EMAIL);
        return $this;
    }
    
    /**
     * No-op, fluent function.
     */
    public function length() {
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    static protected $functionMap = [
        self::OPT_CHECK_LENGTH => 'processCheckLength',
        self::OPT_VALIDATE_EMAIL => 'processValidateEmail'
    ];
    
    protected function processCheckLength($data) {
        $length = mb_strlen($data);
        $minLen = $this->getOption(self::OPT_MIN_LENGTH);
        $maxLen = $this->getOption(self::OPT_MAX_LENGTH);
        
        if (   $length < ($minLen ?: 0)
            || $length > ($maxLen ?: INF))
        {
            if (is_null($minLen)) {
                return new Sentinel("must be at most $maxLen character".($maxLen == 1 ? '' : 's').' long');
            } else if (is_null($maxLen)) {
                return new Sentinel("must be at least $minLen character".($minLen == 1 ? '' : 's').' long');
            } else {
                return new Sentinel("must be $minLen to $maxLen character".($maxLen == 1 ? '' : 's').' long');
            }
        }
        
        return $data;
    }
    
    protected function processValidateEmail($data) {
        // email validation is intentionally lax
        if (preg_match('/^[^@]+@[^@]+\.[^@]+$/', $data) !== 1) {
            return new Sentinel('is invalid');
        }
        return $data;
    }
    
    protected function process($data) {
        if (!is_string($data)) {
            throw new TuduException('Non-string input passed to Validate\String.');
        }
        return $this->apply($data);
    }
}
?>
