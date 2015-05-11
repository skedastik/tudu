<?php
namespace Tudu\Core\Data\Validate;

/**
 * String validator.
 */
class Email extends Validate {
    
    public function __construct() {
        parent::__construct();
        $this->description = "Email address";
    }
    
    protected function _validate($data) {
        // email validation is intentionally lax
        if (preg_match('/^[^@]+@[^@]+\.[^@]+$/', $data) !== 1) {
            return "is invalid.";
        }
        
        return $this->pass($data);
    }
}
?>
