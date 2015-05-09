<?php
namespace Tudu\Core\Handler\Auth;

require_once __DIR__.'/Auth.php';

/**
 * Request handler with HMAC-inspired authentication.
 */
class HMAC extends \Tudu\Core\Handler\Auth\Auth {
    
    protected function authenticate() {
        if (isset($this->context['headers']['Authorization'])) {
            /* TODO: Perform HMAC-inspired authentication. */
        }
        return false;
    }
}
    
?>
