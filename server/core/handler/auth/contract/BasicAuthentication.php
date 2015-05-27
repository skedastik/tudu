<?php
namespace Tudu\Core\Handler\Auth\Contract;

/**
 * Request handler with basic authentication.
 */
final class BasicAuthentication implements Authentication {
    
    public function getScheme() {
        return 'Basic';
    }
    
    public function authenticate($param) {
        /**
         * TODO: Perform basic authentication. See "Basic Authentication
         * Scheme" under RFC2617: http://tools.ietf.org/html/rfc2617
         */
        return false;
    }
}
    
?>
