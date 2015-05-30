<?php
namespace Tudu\Core\Data\Validate;

use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Core;

/**
 * Number validator.
 * 
 * Use various option methods to set validation options.
 */
final class Number extends Validator {
    
    // options
    const OPT_IS_POSITIVE  = 'is_positive';
    
    // Option methods ----------------------------------------------------------
    
    /**
     * Ensure that input is postive.
     */
    public function positive() {
        $this->addOption(self::OPT_IS_POSITIVE);
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    static protected $functionMap = [
        self::OPT_IS_POSITIVE => 'processIsPositive'
    ];
    
    protected function processIsPositive($data) {
        if ($data <= 0) {
            return new Sentinel('must be a positive number');
        }
        return $data;
    }
    
    protected function process($data) {
        if (!is_numeric($data)) {
            throw new Core\Exception('Non-numeric input passed to Validate\Number.');
        }
        return $this->apply($data);
    }
}
?>
