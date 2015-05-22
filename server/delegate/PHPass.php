<?php
namespace \Tudu\Core\Delegate;

use Hautelook\Phpass\PasswordHash;

/**
 * PHPass password hashing delegate.
 */
final class PHPass {
    
    protected $phpass;
    
    public function __construct($password) {
        parent::__construct($password);
        $this->phpass = new PasswordHash(8, false);
    }
    
    function computeHash($password) {
        return $this->phpass->HashPassword($password);
    }
    
    function compare(\Tudu\Core\Delegate\Password $password) {
        return $this->phpass->CheckPassword($this->getHash(), $password->getHash());
    }
}
?>
