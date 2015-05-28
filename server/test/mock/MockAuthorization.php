<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Handler\Auth\Contract\Authorization;

final class MockAuthorization implements Authorization {
    
    public function authorize($requesterId) {
        return $requesterId == 'Jane' ? true : false;
    }
}

?>
