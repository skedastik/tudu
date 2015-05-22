<?php
namespace Tudu\Delegate;

use \Tudu\Core\Delegate\Password;
use \Hautelook\Phpass\PasswordHash;

/**
 * PHPass password hashing delegate.
 */
final class PHPass extends Password {
    
    protected $phpass;
    
    public function __construct() {
        $this->phpass = new PasswordHash(8, false);
    }
    
    protected function computeHash($password) {
        return $this->phpass->HashPassword($password);
    }
    
    public function compare($password, $hash) {
        return $this->phpass->CheckPassword($password, $hash);
    }
}
?>
