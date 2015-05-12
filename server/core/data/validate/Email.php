<?php
namespace Tudu\Core\Data\Validate;

/**
 * String validator.
 */
final class Email extends Validate {
    
    public function __construct() {
        parent::__construct();
        $this->description = "Email address";
    }
    
    protected function process($data) {
        // email validation is intentionally lax
        if (preg_match('/^[^@]+@[^@]+\.[^@]+$/', $data) !== 1) {
            return "is invalid.";
        }
        
        return $this->pass($data);
    }
}
?>
