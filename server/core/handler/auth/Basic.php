<?php
namespace Tudu\Core\Handler\Auth;
/**
 * Request handler with basic authentication.
 */
class Basic extends \Tudu\Core\Handler\Auth\Auth {
    
    protected function authenticate() {
        if (isset($this->context['headers']['Authorization'])) {
            /**
             * TODO: Perform basic authentication. See "Basic Authentication
             * Scheme" under RFC2617:
             * 
             * http://tools.ietf.org/html/rfc2617
             */
        }
        return false;
    }
}
    
?>
