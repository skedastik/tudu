<?php
namespace Tudu\Core\Data\Validate;

use \Tudu\Core\Chainable\Sentinel;

/**
 * Email validator.
 */
final class Email extends Validate {
    
    public function __construct() {
        parent::__construct();
        $this->description = "Email address";
    }
    
    protected function process($data) {
        // email validation is intentionally lax
        if (preg_match('/^[^@]+@[^@]+\.[^@]+$/', $data) !== 1) {
            return new Sentinel('is invalid');
        }
        
        return $data;
    }
}
?>
