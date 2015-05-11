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
        $this->description = "String";
        $this->options = [];
    }
    
    public function length() {
        return $this;
    }
    
    public function from($length) {
        $this->options['min_length'] = $length;
        return $this;
    }
    
    public function upTo($length) {
        $this->options['max_length'] = $length;
        return $this;
    }
    
    protected function _validate($data) {
        $length = strlen($data);
        $minLen = isset($this->options['min_length']) ? $this->options['min_length'] : null;
        $maxLen = isset($this->options['max_length']) ? $this->options['max_length'] : null;
        
        if ($length < (isset($minLen) ? $minLen : 0) || $length > (isset($maxLen) ? $maxLen : INF)) {
            if (!isset($minLen)) {
                return "must be at most $maxLen characters in length.";
            } else if (!isset($maxLen)) {
                return "must be at least $minLen characters in length.";
            } else {
                return "must be $minLen to $maxLen characters in length.";
            }
        }
        
        return $this->pass($data);
    }
}
?>
