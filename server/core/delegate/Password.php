<?php
namespace \Tudu\Core\Delegate;

/**
 * Password hashing delegate.
 */
abstract class Password {
    
    private $hash;
    
    /**
     * Constructor.
     * 
     * @param string $password Plain-text password. The password is hashed
     * immediately and then discarded.
     */
    public function __construct($password) {
        $this->hash = $this->computeHash($password);
    }
    
    /**
     * Compute the password hash.
     * 
     * @param string $password Plain-text password.
     * @return string Computed password hash.
     */
    abstract function computeHash($password);
    
    /**
     * Compare Password against another Password.
     * 
     * @param \Tudu\Core\Delegate\Password $password Input password.
     * @return bool TRUE if hashes resolve to the same password, FALSE
     * otherwise.
     */
    abstract function compare(\Tudu\Core\Delegate\Password $password);
    
    /**
     * Get calculated password hash.
     */
    final public function getHash() {
        return $this->hash;
    }
}
?>
