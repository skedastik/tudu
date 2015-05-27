<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Handler\Auth\Contract\Authentication;

final class MockAuthentication implements Authentication {
    
    public function getScheme() {
        return 'Test';
    }
    
    public function authenticate($param) {
        return true;
    }
}

?>
