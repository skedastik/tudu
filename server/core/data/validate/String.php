<?php
namespace Tudu\Core\Data\Validate;

use \Tudu\Core\Chainable\Sentinel;

/**
 * String validator.
 * 
 * Use various option methods to set validation options.
 */
final class String extends Validate {
    
    protected $options;
    
    public function __construct() {
        parent::__construct();
        $this->description = "String";
        $this->options = [];
    }
    
    /**
     * No-op, fluent function.
     */
    public function length() {
        return $this;
    }
    
    /**
     * Set maximum number of characters allowed.
     */
    public function from($length) {
        $this->options['min_length'] = $length;
        return $this;
    }
    
    /**
     * Set minimum number of characters allowed.
     */
    public function upTo($length) {
        $this->options['max_length'] = $length;
        return $this;
    }
    
    protected function process($data) {
        $length = strlen($data);
        $minLen = isset($this->options['min_length']) ? $this->options['min_length'] : null;
        $maxLen = isset($this->options['max_length']) ? $this->options['max_length'] : null;
        
        if ($length < (isset($minLen) ? $minLen : 0) || $length > (isset($maxLen) ? $maxLen : INF)) {
            if (!isset($minLen)) {
                return new Sentinel("must be at most $maxLen character".($maxLen == 1 ? '' : 's').' in length');
            } else if (!isset($maxLen)) {
                return new Sentinel("must be at least $minLen character".($minLen == 1 ? '' : 's').' in length');
            } else {
                return new Sentinel("must be $minLen to $maxLen character".($maxLen == 1 ? '' : 's').' in length');
            }
        }
        
        return $data;
    }
}
?>
