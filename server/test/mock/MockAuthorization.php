<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Handler\Auth\Contract\Authorization;
use \Tudu\Core\Data\Model;

final class MockAuthorization implements Authorization {
    
    public function authorize(Model $requester) {
        return $requester->get('name') == 'Jane' ? true : false;
    }
}

?>
