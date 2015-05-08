<?php
namespace Tudu\Handler\Frontend;

require_once __DIR__.'/../../core/AuthHandler.php';

/**
 * Request handler for root /
 */
class FrontpageHandler extends \Tudu\Core\AuthHandler {
    protected function acceptAuthentication() {
        echo 'TODO: Send the app!';
    }
    
    protected function rejectAuthentication() {
        echo 'Welcome to Tudu! <a href="signup/">Sign up</a> or <a href="signin/">sign in</a> today!';
    }
}

?>
