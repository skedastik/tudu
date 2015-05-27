<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Handler\Auth\Contract\Authorization;

final class MockAuthorization implements Authorization {
    
    public function authorize($param) {
        return $param == 'Jane' ? true : false;
    }
}

?>
