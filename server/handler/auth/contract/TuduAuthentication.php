<?php
namespace Tudu\Handler\Auth\Contract;

use \Tudu\Core\Handler\Auth\Contract\Authentication;

/**
 * HMAC-inspired Tudu user authentication.
 */
final class TuduAuthentication implements Authentication {
    
    public function getScheme() {
        return 'Tudu';
    }
    
    public function authenticate($param) {
        /**
         * TODO: Reject non-secure requests and immediately revoke access tokens
         * that are sent over unencrypted connections.
         */
        return false;
    }
}
    
?>
