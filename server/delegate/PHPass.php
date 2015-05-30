<?php
namespace Tudu\Delegate;

use \Tudu\Core\Delegate\Password;
use \Hautelook\Phpass\PasswordHash;

/**
 * PHPass password hashing delegate.
 * 
 * Uses Hautelook's modernized version of Openwall's PHPass password hashing
 * library.
 */
final class PHPass extends Password {
    
    protected $phpass;
    
    public function __construct() {
        $this->phpass = new PasswordHash(8, false);
    }
    
    public function computeHash($password) {
        return $this->phpass->HashPassword($password);
    }
    
    public function compare($password, $hash) {
        return $this->phpass->CheckPassword($password, $hash);
    }
}
?>
