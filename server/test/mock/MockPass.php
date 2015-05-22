<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Delegate\Password;

/**
 * Mock password delegate.
 */
final class MockPass extends Password {
    
    protected function computeHash($password) {
        return $password.'*';
    }
    
    public function compare($password, $hash) {
        return $hash == $password.'*';
    }
}
?>
