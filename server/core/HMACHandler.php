<?php
namespace Tudu\Core;

require_once __DIR__.'/AuthHandler.php';

/**
 * Request handler with HMAC-inspired authentication.
 */
class HMACHandler extends AuthHandler {
    
    protected function authenticate() {
        if (isset($this->context['headers']['Authorization'])) {
            /* TODO: Perform HMAC-inspired authentication. */
        }
        return false;
    }
}
    
?>
