<?php
namespace Tudu\Core\Data\Validate;

require_once __DIR__.'/Validate.php';

/**
 * Shorthand factory function.
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
        $this->options = [];
    }
    
    public function length() {
        return $this;
    }
    
    public function from($length) {
        $this->options['min_length'] = $length;
        return $this;
    }
    
    public function to($length) {
        $this->options['max_length'] = $length;
        return $this;
    }
    
    protected function _validate($data) {
        $length = strlen($data);
        $minLen = $this->options['min_length'];
        $maxLen = $this->options['max_length'];
        
        if ($length < $minLen || $length > $maxLen) {
            return "must be $minLen to $maxLen characters in length.";
        }
        
        return $this->pass();
    }
}
?>
