<?php
namespace Tudu\Handler\Frontend;

require_once __DIR__.'/../../core/Handler.php';

/**
 * Request handler for /signin/
 */
class SignupHandler extends \Tudu\Core\Handler {
    public function process() {
        echo 'Sign up here!';
    }
}

?>
