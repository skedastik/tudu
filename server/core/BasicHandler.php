<?php
namespace Tudu\Core;

require_once __DIR__.'/AuthHandler.php';

/**
 * Request handler with basic authentication.
 */
class BasicHandler extends AuthHandler {
    
    protected function authenticate() {
        if (isset($this->context['headers']['Authorization'])) {
            /* TODO: Perform basic authentication. */
        }
        return false;
    }
}
    
?>
