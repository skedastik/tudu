<?php
namespace Tudu\Core\Data\Validate;

require_once __DIR__.'/Validate.php';

/**
 * Shorthand constructor.
 * 
 * @return Tudu\Core\Data\Validate\String A string validator.
 */
function String() {
    return new String();
}

/**
 * String validator.
 */
class String extends Validate {
    
    protected $options;
    
    public function __construct() {
        parent::__construct();
        $this->noun = "String";
        $this->options = [];
    }
    
    public function length() {
        return $this;
    }
    
    public function from($length) {
        $this->options['min_length'] = $length;
        return $this;
    }
    
    public function upto($length) {
        $this->options['max_length'] = $length;
        return $this;
    }
    
    protected function _validate($data) {
        $length = strlen($data);
        $minLen = isset($this->options['min_length']) ? $this->options['min_length'] : 0;
        $maxLen = isset($this->options['max_length']) ? $this->options['max_length'] : PHP_INT_MAX;
        
        if ($length < $minLen || $length > $maxLen) {
            return "must be $minLen to $maxLen characters in length.";
        }
        
        return $this->pass($data);
    }
}
?>
