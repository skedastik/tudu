<?php
namespace Tudu\Core\Handler\Auth;

require_once __DIR__.'/Auth.php';

/**
 * Request handler with basic authentication.
 */
class Basic extends \Tudu\Core\Handler\Auth\Auth {
    
    protected function authenticate() {
        if (isset($this->context['headers']['Authorization'])) {
            /* TODO: Perform basic authentication. */
        }
        return false;
    }
}
    
?>
