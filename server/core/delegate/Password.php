<?php
namespace Tudu\Core\Delegate;

/**
 * Password hashing delegate.
 */
abstract class Password {
    
    /**
     * Compute the hash of a plain text password.
     * 
     * @param string $password Plain-text password.
     * @return string Computed password hash.
     */
    final public function getHash($password) {
        return $this->computeHash($password);
    }
    
    /**
     * Perform the actual hash computation.
     * 
     * @param string $password Plain-text password.
     * @return string Computed password hash.
     */
    abstract protected function computeHash($password);
    
    /**
     * Compare password against computed hash.
     * 
     * @param string $password Plain text password.
     * @param string $hash Computed hash.
     * @return bool TRUE if computed hash resolves to password, FALSE otherwise.
     */
    abstract public function compare($password, $hash);
}
?>
